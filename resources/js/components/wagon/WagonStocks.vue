<script>
import axios from "axios";
import dateformat from "dateformat";
import Loading from "../system/Loading.vue";
import AlertSuccess from "../system/AlertSuccess.vue";
export default {
    name: "WagonStocks",
    components: {
        Loading,
        AlertSuccess
    },
    props: [
        'group',
        'wagonTracks'
    ],
    data() {
        return {
            wagonStocks: [],
            wagonStats: null,
            selectedWagon: null,
            wagon_track_id: null,
            dialog: false,
            systemDialog: false,
            wagon_states: [
                {
                    'name': 'Груженный'
                },
                {
                    'name': 'Порожний'
                }
            ],
            wagon_state: null,
            ordinal_number: null,
            gap: false,
            searchInput: '',
            systemText: null,
            isGrouping: false,
            selectedWagons: [],
            selectedWagonsForm: [],
            groupDialog: false,
            confirmCleaningDialog: false,
            commonTrack: false,
            commonTrackId: null,
            errors: [],
            cleaningWagonTasks: [],
            showLoading: false,
            selectedStates: [],
            selectedTracks: {},
            alert: false,
            alert_text: null,
            add_container_to_wagon_form: false,
            add_container_form: {
                container_number: null,
                container_type: null,
                weight: null,
                consignee_name: null,
                shipper_name: null,
                cargo_name: null,
                seal_number_document: null
            },
            search: ''
        }
    },
    methods: {
        getWagonStocks() {
            this.showLoading = true;
            axios.get('/container-terminals/wagon/get-wagon-stocks?group=' + this.group)
                .then(res => {
                    this.showLoading = false;
                    console.log(res.data)
                    this.wagonStocks = res.data;
                    this.wagonStats = res.data.stats;
                })
                .catch(err => {
                    this.showLoading = false;
                    console.log(err);
                })
        },
        updateWagonStock(){
            this.errors = [];
            let formData = new FormData();

            formData.append('wagon_stock_id', this.selectedWagon.id);

            if (this.$store.getters.isDispatcher) {
                formData.append('wagon_track_id', this.wagon_track_id);
                formData.append('ordinal_number', this.ordinal_number);
                formData.append('gap', this.gap);
            } else {
                formData.append('wagon_state', this.wagon_state);
            }

            formData.append('user_type', (this.$store.getters.isDispatcher) ? 'Dispatcher' : 'ReceiverDeliverer');

            if(this.errors.length === 0) {
                axios.post('/container-terminals/wagon/update-wagon-stock', formData)
                    .then(res => {
                        console.log(res.data);
                        this.systemText = res.data.message;
                        this.dialog = false;
                        this.systemDialog = true;
                        this.wagon_state = null;
                        this.wagon_track_id = null;
                        this.getWagonStocks();
                        setTimeout(() => {
                            this.systemDialog = false;
                        }, 15000);
                    })
                    .catch(err => {
                        console.log(err)
                    })
            }
        },
        updateWagonsStock(){
            this.errors = [];
            let formData = new FormData();

            if(this.$store.getters.isDispatcher) {
                if (this.commonTrack && this.commonTrackId === null) {
                    this.errors.push('Укажите общий путь');
                }

                if(!this.commonTrack) {
                    this.selectedWagonsForm.forEach((row, i) => {
                        const position = i + 1;

                        if(row.wagon_track_id === null) {
                            this.errors.push(`Укажите путь. Позиция №${position}. Вагон: ${row.wagon_number}`);
                        }
                    })
                }
            }

            formData.append('selectedWagonsForm', JSON.stringify(this.selectedWagonsForm));

            if(this.$store.getters.isDispatcher && this.commonTrack) {
                formData.append('commonTrackId', this.commonTrackId);
            }

            formData.append('user_type', (this.$store.getters.isDispatcher) ? 'Dispatcher' : 'ReceiverDeliverer');
            if(this.errors.length === 0) {
                this.showLoading = true;
                axios.post('/container-terminals/wagon/update-wagons-stock', formData)
                    .then(res => {
                        console.log(res.data);
                        this.showLoading = false;
                        this.systemText = res.data.message;
                        this.groupDialog = false;
                        this.isGrouping = false;
                        this.systemDialog = true;
                        this.selectedWagons = [];
                        this.commonTrackId = null;
                        this.commonTrack = null;
                        this.getWagonStocks();
                        setTimeout(() => {
                            this.systemDialog = false;
                        }, 10000);
                    })
                    .catch(err => {
                        this.showLoading = false;
                        console.log(err)
                    })
            }
        },
        getWagonTrackNumberById(trackId){
            let track = this.wagonTracks.find(item => item.id === parseInt(trackId));
            return track ? track.number : null;
        },
        openWagonModal(wagon) {
            this.selectedWagon = wagon;
            this.ordinal_number = wagon.ordinal_number;
            this.dialog = true;
        },
        convertDateToOurFormat(dt){
            return dateformat(dt, 'dd.mm.yyyy HH:MM');
        },
        cancelDialog(){
            this.dialog = false;
            this.wagon_state = null;
            this.wagon_track_id = null;
        },
        toggleGrouping() {
            this.isGrouping = !this.isGrouping;
            this.selectedWagons = [];
        },
        selectWagon(wagon) {
            if (this.selectedWagons.some(w => w.id === wagon.id)) {
                this.selectedWagons = this.selectedWagons.filter(w => w.id !== wagon.id);
            } else {
                this.selectedWagons.push(wagon);
            }
        },
        openGroupModal() {
            this.groupDialog = true;
            this.prepareSelectedWagonsForm();
        },
        cancelGroupDialog(){
            this.groupDialog = false;
        },
        prepareSelectedWagonsForm() {
            this.selectedWagonsForm = this.selectedWagons.map(wagon => {
                return {
                    wagon_stock_id: wagon.id,
                    wagon_number: wagon.wagon_number || '',
                    owner_name: wagon.owner_name || '',
                    wagon_track_id: null,
                    wagon_state: wagon.state ?? null,
                    container: wagon.container ?? '',
                    ordinal_number: wagon.ordinal_number ?? '',
                    wagon_track: wagon.wagon_track,
                    searchInput: "",
                    wagon_status: wagon.status,
                };
            });
        },
        formatTrack(item) {
            if(item.number === '904' || item.number === '905' || item.number === '906') {
                return `${item.number} (${item.company.short_en_name}) выставочный`
            }

            return `${item.number} (${item.company.short_en_name})`
        },
        getClassNameByStatus(status) {
            const map = {
                'Порожний вагон': 'lgrayb',
                'Груж. вагон (с грузом)': 'lorangeb',
                'Груж. вагон (с груж. конт.)': 'lgreenb',
            };
            return map[status] || 'lblueb';
        },
        setClassByDirection(wagon) {
            const map = {
                'Север': 'background-color: brown !important;',
                'Юг': 'background-color: yellow !important;',
                'Алтынколь': 'background-color: magento !important;',
                'Другие': 'background-color: maroon !important;',
            };
            if(wagon.status === 'cleaning_request') {
                return map[wagon.direction] || '';
            }
        },
        confirmCleaning() {
            this.errors = [];
            let formData = new FormData();
            formData.append('selectedWagons', JSON.stringify(this.selectedWagons));
            if(this.errors.length === 0) {
                this.showLoading = true;
                axios.post('/container-terminals/wagon/cleaning-request-wagon', formData)
                    .then(res => {
                        console.log(res)
                        this.showLoading = false;
                        this.confirmCleaningDialog = false;
                        this.isGrouping = false;
                        this.systemText = res.data.message;
                        this.systemDialog = true;
                        this.getWagonStocks();
                        setTimeout(() => {
                            this.systemDialog = false;
                        }, 10000);
                    })
                    .catch(err => {
                        this.showLoading = false;
                        console.log(err)
                    })
            }
        },
        cancelConfirmCleaningDialog() {
            this.confirmCleaningDialog = false;
        },
        printWagonsWithTracks(){
            window.location.href = '/container-terminals/wagon/print-wagons';
        },
        filteredWagonsByState(wagons) {
            if (!this.selectedStates || this.selectedStates.length === 0) {
                return wagons;
            }

            let filterWagons = [];

            // Сопоставляем ключ состояния с backend-значением
            const map = {
                'empty': 'Порожний вагон',
                'loaded': 'Груж. вагон (с грузом)',
                'loaded_container': 'Груж. вагон (с груж. конт.)',
                'empty_container': 'Груж. вагон (с порож. конт.)',
                'shunting_wagons': 'Маневровые работы'
            };

            /*return wagons.filter(wagon =>
                this.selectedStates.some(state => wagon.status_label === map[state])
            );*/

            this.selectedStates.forEach(selectedState => {
                if (selectedState === 'shunting_wagons') {
                    const shun_wagons = wagons.filter(i => i.status === 'moving_request');
                    filterWagons.push(...shun_wagons);
                }
                const www = wagons.filter(i => i.status_label === map[selectedState]);
                filterWagons.push(...www);
            })

            return filterWagons;
        },
        selectedAllWagons(trackId, checked) {
            const wagonsOnTrack = this.wagonStocks.wagons[trackId] || [];
            for (const key in this.selectedTracks) {
                this.selectedTracks[key] = (key === String(trackId));
            }
            this.selectedWagons = this.selectedWagons.filter(w => w.track_id === trackId);
            if (checked) {
                wagonsOnTrack.forEach(wagon => {
                    if (!this.selectedWagons.find(w => w.id === wagon.id)) {
                        this.selectedWagons.push({ ...wagon, track_id: trackId });
                    }
                });
            } else {
                this.selectedWagons = this.selectedWagons.filter(
                    w => w.track_id !== trackId
                );
                this.selectedTracks[trackId] = false;
            }
        },
        addContainerToWagon(wagon){
            this.errors = [];
            let formData = new FormData();

            if(this.add_container_form.container_number == null) {
                this.errors.push('Укажите номер контейнера')
            }

            if(this.add_container_form.container_type == null) {
                this.errors.push('Укажите тип контейнера')
            }

            if(this.add_container_form.shipper_name == null) {
                this.errors.push('Укажите грузоотправителя')
            }

            if(this.$store.getters.isDispatcher && !this.shiftAssigned) {
                this.errors.push('Смены не назначены');
            }

            formData.append('data', JSON.stringify(this.add_container_form))
            formData.append('wagon', JSON.stringify(wagon));
            if(this.errors.length === 0) {
                this.showLoading = true;
                axios.post('/container-terminals/wagon/attach-container-to-wagon', formData)
                    .then(res => {
                        console.log(res)
                        this.add_container_form.container_number = null;
                        this.add_container_form.container_type = null;
                        this.add_container_form.shipper_name = null;
                        this.add_container_form.consignee_name = null;
                        this.add_container_form.cargo_name = null;
                        this.add_container_form.seal_number_document = null;
                        this.add_container_form.weight = null;
                        this.add_container_to_wagon_form = false;
                        this.getWagonStocks();
                        this.showLoading = false;
                        this.alert_text = res.data.message;
                        this.selectedWagon.containers = res.data.containers;
                        this.alert = true;
                        setTimeout(() => (this.alert = false), 5000);
                    })
                    .catch(err => {
                        console.log(err)
                        this.showLoading = false;
                    })
            }
        },
        detachContainer(wagonStockContainerId, container_id){
            this.showLoading = true;
            this.errors = [];

            if(this.$store.getters.isDispatcher && !this.shiftAssigned) {
                this.errors.push('Смены не назначены');
            }

            if(this.errors.length === 0) {
                axios.delete('/container-terminals/wagon/detach-container-from-wagon/' + wagonStockContainerId)
                    .then(res => {
                        this.selectedWagon.containers = this.selectedWagon.containers.filter(
                            (item) => item.container_id !== container_id
                        );
                        this.getWagonStocks();
                        this.showLoading = false;
                        this.alert_text = res.data.message;
                        this.alert = true;
                        setTimeout(() => (this.alert = false), 5000);
                    })
                    .catch(err => {
                        this.showLoading = false;
                        this.errors.push(
                            err.response?.data?.message || 'Ошибка при откреплении контейнера'
                        );
                    })
            }
        },
        getWagonState(wagon){
            const map = {
                'Порожний вагон': 'пор.',
                'Груж. вагон (с грузом)': 'гр.',
                'Груж. вагон (с груж. конт.)': 'гр.',
            };
            return map[wagon.status_label] || 'гр.';
        },
        getWagonTrackCompany(trackId){
            let track = this.wagonTracks.find(w => w.id === parseInt(trackId));
            return track?.company?.short_ru_name;
        },
    },
    computed: {
        isCleaningButtonActive() {
            if (!this.wagonStocks || !this.wagonStocks.wagons)
                return false;

            if (this.isGrouping) {
                for (const trackId in this.selectedTracks) {
                    if(this.selectedTracks[trackId]) {
                        if ([3, 4, 5].includes(Number(trackId))) {
                            if (this.selectedWagons.some(w => w.status !== 'cleaning_request')) {
                                return false;
                            } else {
                                return true;
                            }
                        }
                    }
                }
            }

            return false;
        },
        filteredWagonStocks() {
            if (!this.search || !this.wagonStocks?.wagons) {
                return this.wagonStocks.wagons || {};
            }

            const term = this.search.toLowerCase();
            const filtered = {};

            for (const [trackId, wagons] of Object.entries(this.wagonStocks.wagons)) {
                // фильтруем вагоны по всем полям
                const filteredWagons = wagons.filter(wagon => {
                    return Object.values(wagon).some(value => {
                        if (value == null) return false;
                        return String(value).toLowerCase().includes(term);
                    });
                });

                // добавляем только те пути, где есть совпадения
                if (filteredWagons.length > 0) {
                    filtered[trackId] = filteredWagons;
                }
            }

            return filtered;
        },
        shiftAssigned(){
            return this.$store.getters.currentShifts && this.$store.getters.currentShifts.length > 0;
        },
        activateActions(){
            if (this.$store.getters.isReceiverDeliverer && this.groupDialog) {
                this.selectedWagonsForm.some(item => {
                    if (item.wagon_status === 'submission') return false;
                    return true;
                });
            }

            if (this.$store.getters.isReceiverDeliverer && this.dialog) {
                if ([3, 4, 5].includes(this.selectedWagon?.wagon_track_id)) {
                    return false;
                }
                return true;
            }

            if (this.$store.getters.isDispatcher) {
                return true;
            }
        }
    },
    created(){
        this.getWagonStocks();
    }
}
</script>

<template>
    <div>

        <Loading :show="showLoading"></Loading>

        <transition name="fade">
            <div class="system-info" v-if="systemDialog">
                <v-icon color="green">mdi-checkbox-marked-circle-outline</v-icon>
                <span v-html="systemText"></span>
                <v-icon @click="systemDialog=false">mdi-close</v-icon>
            </div>
        </transition>

        <div class="horizontal-buttons-square">
            <div style="display: flex; gap: 20px;">
                <div>
                    <v-btn outlined @click="toggleGrouping">
                        {{ isGrouping ? 'Отмена' : 'Группировать' }}
                    </v-btn>

                    <v-btn v-if="isGrouping && selectedWagons.length > 1" outlined @click="openGroupModal">
                        Действие
                    </v-btn>

                    <v-btn v-if="isCleaningButtonActive && selectedWagons.length > 0" color="green" outlined @click="confirmCleaningDialog = !confirmCleaningDialog">
                        Подтвердить уборку
                    </v-btn>
                </div>

                <div>
                    <v-btn color="green" outlined @click="printWagonsWithTracks">
                        Распечатать
                    </v-btn>
                </div>
            </div>

            <div>
                <v-text-field
                    v-model="search"
                    density="compact"
                    label="Search"
                    prepend-inner-icon="mdi-magnify"
                    variant="solo-filled"
                    flat
                    hide-details
                    single-line
                ></v-text-field>
            </div>

            <div class="memory-blocks">
                <div class="memory-block-item">
                    <span style="background-color: brown;"></span>Север
                </div>
                <div class="memory-block-item">
                    <span style="background-color: yellow;"></span>Юг
                </div>
                <div class="memory-block-item">
                    <span style="background-color: magenta;"></span>Алтынколь
                </div>
                <div class="memory-block-item">
                    <span style="background-color: maroon;"></span>Другие
                </div>
            </div>
        </div>

        <div class="horizontal-wagons-stats">
            <div class="wagon-stat-item" style="border-left: 4px solid grey;">
                <v-checkbox
                    v-model="selectedStates"
                    :label="`Порожний вагон - ${wagonStats?.empty_wagons} в.`"
                    color="grey"
                    value="empty"
                    hide-details
                ></v-checkbox>
            </div>

            <div class="wagon-stat-item" style="border-left: 4px solid orange; background: #FFE4B2;">
                <v-checkbox
                    v-model="selectedStates"
                    :label="`Груж. вагон (с грузом) - ${wagonStats?.loaded_wagons} в.`"
                    color="orange"
                    value="loaded"
                    hide-details
                ></v-checkbox>
            </div>

            <div class="wagon-stat-item" style="background: #C8F0C8; border-left: 4px solid green;">
                <v-checkbox
                    v-model="selectedStates"
                    :label="`Груж. вагон (с груж. конт.) - ${wagonStats?.loaded_with_loaded_container} в.`"
                    color="green"
                    value="loaded_container"
                    hide-details
                ></v-checkbox>
            </div>

            <div class="wagon-stat-item" style="border-left: 4px solid blue; background: #D5D5FF;">
                <v-checkbox
                    v-model="selectedStates"
                    :label="`Груж. вагон (с порож. конт.) - ${wagonStats?.loaded_with_empty_container} в.`"
                    color="blue"
                    value="empty_container"
                    hide-details
                ></v-checkbox>
            </div>

            <div class="wagon-stat-item" style="border-left: 4px solid hotpink; background: #FFADD6;">
                <v-checkbox
                    v-model="selectedStates"
                    :label="`Маневровые работы - ${wagonStats?.shunting_wagons} в.`"
                    color="blue"
                    value="shunting_wagons"
                    hide-details
                ></v-checkbox>
            </div>

            <div style="display: flex; align-items: end; ">
                <span style="text-decoration: underline;">Всего - {{ wagonStats?.all_wagons }} в.</span>
            </div>
        </div>

        <div id="print-area" class="flex gap-4 overflow-x-auto">
            <div v-for="(wagons, trackId) in filteredWagonStocks" :key="trackId" class="w-64 bg-gray-100 rounded wagonTrack">
                <div class="wagonTrackHeader">
                    <h6 class="font-bold text-center" style="margin-bottom: 0;">Путь {{ getWagonTrackNumberById(trackId) }}</h6>
                    <span>{{ getWagonTrackCompany(trackId) }} ({{ Object.keys(wagons).length }} в.)</span>
                    <div style="display: flex; justify-content: center;">
                        <v-checkbox
                            v-if="isGrouping"
                            v-model="selectedTracks[trackId]"
                            label="Все"
                            color="blue"
                            hide-details
                            dense
                            @change="selectedAllWagons(trackId, selectedTracks[trackId])"
                        ></v-checkbox>
                    </div>
                </div>
                <div class="wagonContainer">
                    <div v-for="wagon in filteredWagonsByState(wagons)"
                         :key="wagon.id" class="bg-white p-2 m-1 shadow wagonItem"
                         :style="setClassByDirection(wagon)"
                         :class="[
                         getClassNameByStatus(wagon.status_label),
                         selectedWagons.includes(wagon.id) ? 'selectedWagon' : '',
                         wagon.gap === 1 ? 'gap' : ''
                     ]"
                         @click="isGrouping ? selectWagon(wagon) : openWagonModal(wagon)">
                        <div class="wagonItemBox">
                            <div style="display: flex; justify-content: space-between;">
                                <p><strong>{{ wagon.ordinal_number }}) {{ wagon.wagon_number }} ({{ getWagonState(wagon) }})</strong></p>
                                <v-icon size="medium" v-if="isGrouping" color="green">
                                    {{ selectedWagons.some(w => w.id === wagon.id) ? 'mdi-checkbox-marked' : 'mdi-checkbox-blank-outline' }}
                                </v-icon>
                            </div>
                            <p v-if="wagon.status === 'submission' && parseInt(trackId) !== wagon.destination_track?.id" style="font-size: 12px;">Цел: {{ wagon.destination_track?.number ?? 'не задан' }}</p>
                            <span v-if="wagon.status === 'cleaning_request'" class="cleaning-dot"></span>
                            <span v-if="wagon.status === 'moving_request'" class="moving-dot"></span>
                        </div>

                        <div class="tooltip">
                            <p style="border-bottom: 1px dashed;">
                                <strong>№ вагона - </strong> {{ wagon.wagon_number }}
                                <span v-if="wagon.status === 'cleaning_request'">на уборку</span>
                                <span v-if="wagon.status === 'submission'">на подачу</span>
                            </p>
                            <p>
                                <strong>Цель(направление):</strong>
                                <span>{{ wagon.destination_track?.number ?? 'не задан' }}</span>
                            </p>
                            <p><strong>№п/п</strong><span>{{ wagon.ordinal_number }}</span></p>
                            <p>
                                <strong>Длина вагона:</strong>
                                <span>{{ wagon.wagon_length }} (м)</span>
                            </p>
                            <p><strong>Собственник:</strong> {{ wagon.owner_name ?? '—' }}</p>
                            <p><strong>Состояние:</strong> {{ wagon.status_label }}</p>
                            <p v-if="wagon.container_number"><strong>Контейнер:</strong> {{ wagon.container_number }}</p>
                            <p><strong>Дата подачи:</strong> {{ convertDateToOurFormat(wagon.reception_datetime) }}</p>
                            <p><strong>Дата операции (посл):</strong> {{ convertDateToOurFormat(wagon.updated_at) }}</p>
                            <p>
                                <span v-if="wagon.status === 'cleaning_request'">
                                    <i style="color: red; font-size: 14px;" class="fa fa-exclamation-circle"></i>&nbsp;Есть заявка на уборку
                                </span>
                                <span v-if="wagon.status === 'moving_request'">
                                    <i style="color: green; font-size: 14px;" class="fa fa-exclamation-circle"></i>&nbsp;Есть заявка на маневровые работы
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <v-dialog style="z-index: 99999; position: relative; margin-top: 10% !important;" v-model="dialog" persistent max-width="800px">
                <v-card>
                    <v-card-title>
                        <span class="headline" style="font-size: 40px !important;">Вагон: {{ selectedWagon?.wagon_number }}</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <p><strong>Статус: </strong> {{ selectedWagon?.status_label }}</p>
                                </v-col>
                                <v-col cols="4" v-if="$store.getters.isReceiverDeliverer && activateActions">
                                    <v-select
                                        :items="wagon_states"
                                        item-value="name"
                                        item-text="name"
                                        outlined
                                        v-model="wagon_state"
                                        dense
                                        label="Укажите состояние"
                                    ></v-select>
                                </v-col>

                                <v-col cols="4" v-if="$store.getters.isDispatcher">
                                    <v-autocomplete
                                        :items="wagonTracks"
                                        :hint="`${wagonTracks.id}, ${wagonTracks.number}`"
                                        :search-input.sync="searchInput"
                                        item-value="id"
                                        v-model="wagon_track_id"
                                        :item-text="item => `${item.number} (${item.company.short_en_name})`"
                                        label="Укажите путь"
                                        autocomplete
                                        outlined
                                        dense
                                    ></v-autocomplete>
                                </v-col>

                                <v-col cols="4" v-if="$store.getters.isDispatcher">
                                    <v-text-field
                                        label="№ п/п"
                                        v-model="ordinal_number"
                                        clearable
                                        outlined
                                        dense
                                    ></v-text-field>
                                </v-col>

                                <v-col cols="4" v-if="$store.getters.isDispatcher">
                                    <v-checkbox
                                        v-model="gap"
                                        label="Разрыв"
                                        color="blue"
                                        hide-details
                                        dense
                                    ></v-checkbox>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12">
                                    <div v-if="activateActions" class="wagon-item-actions">
                                        <h4>Список контейнеров</h4>
                                        <v-btn v-if="selectedWagon?.status !== 'cleaning_request' && $store.getters.isReceiverDeliverer" outlined color="green" @click="add_container_to_wagon_form = !add_container_to_wagon_form">
                                            {{ add_container_to_wagon_form ? 'Скрыть форму' : 'Показать форму' }}
                                        </v-btn>
                                    </div>
                                    <div v-if="add_container_to_wagon_form" class="wagon-add-container-form">
                                        <div class="add-container-form-row">
                                            <div class="w-2">
                                                <v-text-field
                                                    label="Контейнер"
                                                    outlined
                                                    v-model="add_container_form.container_number"
                                                ></v-text-field>
                                            </div>
                                            <div class="w-1">
                                                <v-select
                                                    :items="['40', '45', '20']"
                                                    label="Тип"
                                                    outlined
                                                    v-model="add_container_form.container_type"
                                                ></v-select>
                                            </div>
                                            <div class="w-1">
                                                <v-text-field
                                                    label="Вес"
                                                    outlined
                                                    v-model="add_container_form.weight"
                                                ></v-text-field>
                                            </div>
                                            <div class="w-2">
                                                <v-text-field
                                                    label="Грузополучатель"
                                                    outlined
                                                    v-model="add_container_form.consignee_name"
                                                ></v-text-field>
                                            </div>
                                        </div>

                                        <div class="add-container-form-row">
                                            <div>
                                                <v-text-field
                                                    label="Грузоотправитель"
                                                    outlined
                                                    v-model="add_container_form.shipper_name"
                                                ></v-text-field>
                                            </div>
                                            <div>
                                                <v-text-field
                                                    label="Наи. груза"
                                                    outlined
                                                    v-model="add_container_form.cargo_name"
                                                ></v-text-field>
                                            </div>
                                            <div>
                                                <v-text-field
                                                    label="Номер пломбы"
                                                    outlined
                                                    v-model="add_container_form.seal_number_document"
                                                ></v-text-field>
                                            </div>
                                        </div>

                                        <div style="text-align: right; margin-bottom: 10px;">
                                            <v-btn outlined color="orange" @click="addContainerToWagon(selectedWagon)">Добавить контейнер</v-btn>
                                        </div>
                                    </div>
                                    <div v-if="selectedWagon && selectedWagon.containers && selectedWagon.containers.length" class="wagon-containers">
                                        <div v-for="(wagonStockContainer, i) in selectedWagon.containers" :key="i" class="wagon-container-item">
                                            <div>
                                                <p><strong>Контейнер:</strong> {{ wagonStockContainer.container_number }}</p>
                                                <p><strong>Вес:</strong> {{ wagonStockContainer.weight }}</p>
                                                <p><strong>Грузополучатель:</strong> {{ wagonStockContainer.consignee_name }}</p>
                                            </div>

                                            <div style="display: flex; align-items: center;">
                                                <v-btn v-if="selectedWagon?.status !== 'cleaning_request' && $store.getters.isReceiverDeliverer && activateActions" outlined color="orange" @click="detachContainer(wagonStockContainer.id, wagonStockContainer.container_id)">Снять контейнер</v-btn>
                                            </div>
                                        </div>
                                    </div>
                                </v-col>

                                <v-col v-if="errors.length" cols="12">
                                    <p style="margin-bottom: 0px !important;">
                                        <b>Пожалуйста исправьте указанные ошибки:</b>
                                        <ul style="color: #cc0000; padding-left: 15px; list-style: circle; text-align: left;">
                                            <li v-for="error in errors">{{error}}</li>
                                        </ul>
                                    </p>
                                </v-col>

                                <AlertSuccess v-if="alert" :message="alert_text"></AlertSuccess>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" @click="cancelDialog()">Отменить</v-btn>
                        <v-btn v-if="activateActions" color="success darken-1" @click="updateWagonStock()">
                            <v-icon
                                middle
                            >
                                mdi-save
                            </v-icon>
                            &nbsp;Сохранить
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-dialog style="z-index: 99999; position: relative; margin-top: 10% !important;" v-model="groupDialog" persistent max-width="800px">
                <v-card>
                    <v-card-title>
                        <span class="headline" style="font-size: 40px !important;">Действие над вагонами</span>
                    </v-card-title>
                    <v-card-text style="max-height: 700px; overflow-y: auto;">
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <div v-if="$store.getters.isDispatcher" class="commonFilter">
                                        <label for="checkbox1">
                                            <input type="checkbox" id="checkbox1" v-model="commonTrack"> Путь общий ?
                                        </label>

                                        <v-autocomplete
                                            v-if="commonTrack"
                                            :items="wagonTracks"
                                            item-value="id"
                                            v-model="commonTrackId"
                                            :item-text="formatTrack"
                                            label="Путь Назначения"
                                            autocomplete
                                            outlined
                                            dense
                                            style="margin-top: 10px !important;"
                                        ></v-autocomplete>
                                    </div>

                                    <div v-for="(wagon, i) in selectedWagonsForm" :key="i" class="wagon-list mb-4">
                                        <div class="wagon-info mb-2">
                                            <strong>Вагон:</strong> {{ wagon.wagon_number }}
                                            <strong>Клиент:</strong> {{ wagon.owner_name }}
                                            <strong>Текущий Путь:</strong> {{ wagon.wagon_track }}
                                        </div>

                                        <div class="wagon-item-actions">
                                            <div v-if="!commonTrack && $store.getters.isDispatcher">
                                                <v-autocomplete
                                                    :items="wagonTracks"
                                                    :search-input.sync="wagon.searchInput"
                                                    item-value="id"
                                                    v-model="wagon.wagon_track_id"
                                                    :item-text="formatTrack"
                                                    label="Путь Назначения"
                                                    autocomplete
                                                    outlined
                                                    dense
                                                ></v-autocomplete>
                                            </div>
                                            <div v-if="$store.getters.isReceiverDeliverer" class="wagon-state">
                                                <v-select
                                                    :items="wagon_states"
                                                    item-value="name"
                                                    item-text="name"
                                                    outlined
                                                    v-model="wagon.wagon_state"
                                                    dense
                                                    label="Состояние"
                                                    :disabled="wagon.wagon_status === 'submission'"
                                                ></v-select>
                                            </div>

                                            <div v-if="$store.getters.isDispatcher" class="wagon-serial">
                                                <v-text-field
                                                    label="№ п/п"
                                                    v-model="wagon.ordinal_number"
                                                    clearable
                                                    outlined
                                                    dense
                                                ></v-text-field>
                                            </div>
                                        </div>

                                    </div>
                                </v-col>

                                <v-col v-if="errors.length" cols="12">
                                    <p style="margin-bottom: 0px !important;">
                                        <b>Пожалуйста исправьте указанные ошибки:</b>
                                        <ul style="color: #cc0000; padding-left: 15px; list-style: circle; text-align: left;">
                                            <li v-for="error in errors">{{error}}</li>
                                        </ul>
                                    </p>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" @click="cancelGroupDialog">Отменить</v-btn>
                        <v-btn v-if="activateActions" color="success darken-1" @click="updateWagonsStock()">
                            <v-icon
                                middle
                            >
                                mdi-save
                            </v-icon>
                            &nbsp;Сохранить
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-dialog style="z-index: 99999; position: relative; margin-top: 10% !important;" v-model="confirmCleaningDialog" persistent max-width="800px">
                <v-card>
                    <v-card-title>
                        <span class="headline" style="font-size: 40px !important;">Подтвердить уборку</span>
                    </v-card-title>
                    <v-card-text style="max-height: 700px; overflow-y: auto;">
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <div class="commonFilter">
                                        Вы уверены, что хотите подтвердить уборку?
                                    </div>

                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" @click="cancelConfirmCleaningDialog">Отменить</v-btn>
                        <v-btn color="success darken-1" @click="confirmCleaning">
                            <v-icon
                                middle
                            >
                                mdi-save
                            </v-icon>
                            &nbsp;Да, подтвердить
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </div>
    </div>
</template>

<style scoped>
.wagon-add-container-form {
    .add-container-form-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 10px;

        .w-1 {
            flex: 1;
        }
        .w-2 {
            flex: 2;
        }
    }
}
.wagon-item-actions {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}
.wagon-container-item {
    padding: 10px;
    border: 1px dashed;
    border-radius: 5px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
}
.v-label {
    font-size: 12px;
    font-weight: normal;
}
.horizontal-wagons-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    border-bottom: 1px solid;
    padding-bottom: 10px;

    .wagon-stat-item {
        border: 1px solid #ccc;
        padding: 2px;
    }
}

.commonFilter {
    margin-bottom: 20px;

    label {
        font-size: 16px;
        font-weight: normal;
        cursor: pointer;
    }
}

.wagonTrackHeader {
    border: 1px solid #ccc;
    padding: 2px;
    margin-bottom: 10px;
}

.wagon-list {
    padding: 10px;
    border: 1px dashed;

    .wagon-item-actions {
        display: flex;
        flex-direction: row;
        gap: 20px;
        margin: 10px 0;

        .wagon-state {
            flex: 2;
        }
        .wagon-serial {
            flex: 2;
        }
        .wagon-container {
            flex: 3;
        }
    }
}
.wagonItemBox {
    text-align: left;
    display: flex;
    flex-direction: column;
}

.system-info {
    display: flex;
    gap: 20px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    width: 350px;
    position: absolute;
    right: 10px;
    padding: 10px 20px;
    justify-content: space-between;
}
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.flex {
    display: flex;
}
.v-application p {
    margin-bottom: 2px;
}
.wagonTrack{
    text-align: center;
    min-height: 100vh;

    .wagonContainer {
        padding: 10px 5px;
        border: 1px solid #ccc;
    }
}
.wagonItem {
    position: relative;
    border: 1px solid #ccc;
    cursor: pointer;
    width: 175px;
}
.lgreenb {
    border-left: 4px solid green;
}
.lgrayb {
    border-left: 4px solid gray;
}
.lorangeb {
    border-left: 4px solid orange;
}
.lblueb {
    border-left: 4px solid blue;
}
.wagonItem:hover > div:last-child {
    opacity: 1;
    pointer-events: auto;
}
.tooltip {
    position: absolute;
    top: 0;
    left: 100%;
    margin-left: 8px;
    background: white;
    border: 1px solid #ccc;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    padding: 6px 10px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 10;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s;
}
.wagonItem:hover .tooltip {
    opacity: 1;
    visibility: visible;
}
.tooltip::before {
    content: "";
    position: absolute;
    top: 10px;
    left: -14px;
    border: 7px solid transparent;
    border-right: 10px solid white;
}
.selectedWagon {
    /*border: 2px solid blue !important;
    background-color: #eaf4ff;*/
}
.horizontal-buttons-square {
    margin: 10px 0;
    display: flex;
    justify-content: space-between;

    .memory-blocks {
        display: flex;
        gap: 30px;

        .memory-block-item {
            span {
                display: inline-block;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                margin-right: 20px;
            }
        }
    }
}
.cleaning-dot {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 14px;
    height: 14px;
    background-color: red;
    border-radius: 50%;
    box-shadow: 0 0 10px red;
    animation: pulse 1s infinite;
}

.moving-dot {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 14px;
    height: 14px;
    background-color: green;
    border-radius: 50%;
    box-shadow: 0 0 10px green;
    animation: pulse 1s infinite;
}
.gap {
    margin-bottom: 50px !important;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.4);
        opacity: 0.6;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
