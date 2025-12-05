<script>
import dateformat from "dateformat";
import axios from "axios";
import {mapActions} from "vuex";
import CargoCreateModal from "../../components/modals/CargoCreateModal.vue";
import CargoTaskCompleteModal from "../../components/modals/CargoTaskCompleteModal.vue";
import CargoPositionModal from "../../components/modals/CargoPositionModal.vue";
import WagonCreateTaskModal from "./WagonCreateTaskModal.vue";
import Information from "../system/Information.vue";
export default {
    components: {
        CargoPositionModal,
        CargoCreateModal,
        CargoTaskCompleteModal,
        WagonCreateTaskModal,
        Information
    },
    data() {
        return {
            wagonHeaders: [
                {
                    text: '№ заявки',
                    align: 'start',
                    sortable: true,
                    value: 'id',
                },
                { text: 'Создал', value: 'user.full_name'},
                { text: 'Тип', value: 'type'},
                { text: 'Статус', value: 'status' },
                { text: 'Файл', value: 'upload_file' },
                { text: 'Дата', value: 'date_time' },
                { text: 'Действие', value: 'action' },
                { text: '', value: 'data-table-expand' }
            ],
            wagonTasks: [],
            search: '',
            isLoaded: true,
            dialog: false,
            dialogEdit: false,
            cargoTask: null,
            expanded: [],
            singleExpand: false,
            statuses: [
                { 'open': 'Новая' },
                { 'processing': 'В работе' },
                { 'closed': 'Завершен' },
                { 'canceled': 'Отменен' }
            ],
            systemText: '',
            showInfo: false,
            headers: [
                {
                    text: '№ заявки',
                    align: 'start',
                    sortable: true,
                    value: 'id',
                },
                { text: 'Тип', value: 'task_type'},
                { text: 'Тип авто', value: 'trans_type'},
                { text: 'Статус', value: 'status' },
                { text: 'Файл', value: 'upload_file' },
                { text: 'История', value: 'import_logs' },
                { text: 'Ред.', value: 'edit' },
                { text: 'Дата', value: 'created_at' },
                { text: '', value: 'data-table-expand' }
            ],
        }
    },
    methods: {
        ...mapActions(['showWagonModal']),
        convertDateToOurFormat(dt){
            return dateformat(dt, 'dd.mm.yyyy HH:MM');
        },
        statusLabel(status){
            const found = this.statuses.find(s => Object.keys(s)[0] === status)
            return found ? Object.values(found)[0] : 'Неизвестный статус'
        },
        getWagonTasks(){
            this.wagonTasks = [];
            this.isLoaded = true;

            axios.get('/container-terminals/wagon/get-wagon-tasks')
                .then(res => {
                    console.log(res);
                    this.wagonTasks = res.data;
                    this.isLoaded = false;
                })
                .catch(err => {
                    console.log(err);
                })
        },
        getRowClass(item) {
            if (item.isClose) return 'closed';
            return '';
        },
        completedCargoTask(item){
            this.cargoTask = item;
            this.dialog = true;
        },
        editCargoTask(item){
            this.cargoTask = item;
            this.dialogEdit = true;
            console.log(item)
        },
        wagonCreateTaskModal(data) {
            this.systemText = data.systemText;
            this.showInfo = true;
            this.getWagonTasks();

            setTimeout(() => {
                this.showInfo = false;
                this.systemText = '';
            }, 10000);
        },
        closeInformation(){
            this.showInfo = false;
            this.systemText = '';
        }
    }
}
</script>

<template>
    <v-card flat>
        <v-btn
            color="primary"
            outlined
            @click="showWagonModal"
        >Создать заявку</v-btn>

        <WagonCreateTaskModal
            @wagon-create-task-modal="wagonCreateTaskModal">
        </WagonCreateTaskModal>

        <Information
            v-if="showInfo"
            :systemText="systemText"
            @close-information="closeInformation">
        </Information>

        <v-card-title class="d-flex align-center pe-2">
            <v-icon icon="mdi-video-input-component"></v-icon> &nbsp;
            Список заявки

            <v-spacer></v-spacer>

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
        </v-card-title>

        <v-data-table
            :headers="wagonHeaders"
            :items="wagonTasks"
            :items-per-page="20"
            :search="search"
            :loading="isLoaded"
            :single-expand="singleExpand"
            :expanded.sync="expanded"
            show-expand
            class="elevation-1"
            loading-text="Загружается... Пожалуйста подождите"
            :item-class="getRowClass"
        >
            <template v-slot:item.id="{ item }">
                <span v-if="item.type === 'submission'">{{ 'IN_'+ item.id }}</span>
                <span v-if="item.type === 'cleaning'">{{ 'OUT_'+ item.id }}</span>
                <span v-if="item.type === 'shunting'">{{ 'MOV_'+ item.id }}</span>
            </template>

            <template v-slot:item.type="{ item }">
                <span v-if="item.type === 'submission'">Подача</span>
                <span v-if="item.type === 'cleaning'">Уборка</span>
                <span v-if="item.type === 'shunting'">Маневровые</span>
            </template>

            <template v-slot:item.status="{ item }">
                <div v-if="item.status === 'open'">
                    Новый
                </div>
                <div v-else-if="item.status === 'processing'">
                    В работе
                </div>
                <div v-else-if="item.status === 'closed'">
                    Завершен
                </div>
                <div v-else-if="item.status === 'canceled'">
                    Отменен
                </div>
                <div v-else-if="item.status === 'failed'">
                    Ошибка
                </div>
                <div v-else>
                    Игнарирован
                </div>
            </template>

            <template v-slot:item.upload_file="{ item }">
                <div v-if="item.upload_file !== null">
                    <a :href="item.upload_file" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp;Скачать</a>
                </div>
            </template>

            <template v-slot:item.date_time="{ item }">
                {{ convertDateToOurFormat(item.date_time) }}
            </template>

            <template v-slot:expanded-item="{ headers, item }">
                <td :colspan="headers.length" style="padding: 10px 20px;">
                    <p style="margin-bottom: 15px;"><strong>Информация по заявке: </strong> {{ item.number }}</p>
                    <table v-if="item.type === 'submission'" class="table table-bordered wagonTaskItems">
                        <thead>
                        <th>№П/П</th>
                        <th>Вагон</th>
                        <th>Длина вагона</th>
                        <th>Контейнер</th>
                        <th>Номер пути <br>(выставочный)</th>
                        <th>Номер пути <br>(цель/направление)</th>
                        <th>Наименование <br>(собственника/арендатора)</th>
                        <th>Наименование груза</th>
                        <th>Наименование <br>грузополучателя</th>
                        <th>Статус</th>
                        <th>Дата и время <br>подачи/приема</th>
                        </thead>

                        <tbody>
                            <tr v-for="(wagonTaskItem, i) in item.items" :key="i">
                                <td><span>{{ wagonTaskItem.ordinal_number }}</span></td>
                                <td>
                                    <span>{{ wagonTaskItem.wagon_number }}</span>
                                </td>
                                <td>
                                    <span>{{ wagonTaskItem.wagon?.length || 'не задан' }} (м)</span>
                                </td>
                                <td>
                                    <span>{{ wagonTaskItem.wagon_containers_number }}</span>
                                </td>
                                <td>
                                    <span>{{ wagonTaskItem.wagon_track?.number || '-' }}</span>
                                </td>
                                <td>
                                    <span>{{ wagonTaskItem.wagon_track_destination?.number || '-' }}</span>
                                </td>
                                <td>{{ wagonTaskItem.owner_name }}</td>
                                <td>{{ wagonTaskItem.cargo_name }}</td>
                                <td>{{ wagonTaskItem.consignee_name }}</td>
                                <td>{{ wagonTaskItem.wagon_state_label }}</td>
                                <td>{{ convertDateToOurFormat(wagonTaskItem.reception_datetime) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <table v-if="item.type === 'cleaning'" class="table table-bordered wagonTaskItems">
                        <thead>
                        <th>Вагон</th>
                        <th>Длина вагона</th>
                        <th>Контейнер</th>
                        <th>Номер пути</th>
                        <th>Станция назначения</th>
                        <th>Направления</th>
                        <th>Наименование <br>грузоотправителя</th>
                        <th>Статус</th>
                        </thead>

                        <tbody>
                        <tr v-for="(wagonTaskItem, i) in item.items" :key="i">
                            <td>
                                <span>{{ wagonTaskItem.wagon_number }}</span>
                            </td>
                            <td>
                                <span>{{ wagonTaskItem.wagon?.length || '-' }}м</span>
                            </td>
                            <td>
                                <span>{{ wagonTaskItem.wagon_containers_number }}</span>
                            </td>
                            <td>
                                <span>{{ wagonTaskItem.wagon_track?.number || '-' }}</span>
                            </td>
                            <td>
                                <span>{{ wagonTaskItem.destination_station }}</span>
                            </td>
                            <td>{{ wagonTaskItem.direction }}</td>
                            <td>{{ wagonTaskItem.shipper_name }}</td>
                            <td>{{ wagonTaskItem.wagon_state_label }}</td>
                        </tr>
                        </tbody>
                    </table>

                    <table v-if="item.type === 'shunting'" class="table table-bordered wagonTaskItems">
                        <thead>
                        <th>Вагон</th>
                        <th>Длина вагона</th>
                        <th>Текущий (путь)</th>
                        <th>Цель (путь)</th>
                        <th>Наименование <br>грузополучателя</th>
                        <th>Статус</th>
                        </thead>

                        <tbody>
                        <tr v-for="(wagonTaskItem, i) in item.items" :key="i">
                            <td>
                                <span>{{ wagonTaskItem.wagon_number }}</span>
                            </td>
                            <td>
                                <span>{{ wagonTaskItem.wagon?.length || '-' }}м</span>
                            </td>
                            <td>
                                <span>{{ wagonTaskItem.wagon_track?.number || '-' }}</span>
                            </td>
                            <td>{{ wagonTaskItem.wagon_track_destination?.number || '-' }}</td>
                            <td>{{ wagonTaskItem.consignee_name }}</td>
                            <td>{{ wagonTaskItem.wagon_state_label }}</td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </template>

        </v-data-table>
    </v-card>
</template>

<style>
.wagonTaskItems {
    th {
        text-align: center;
        align-content: center;
    }
    td {
        text-align: center;
    }
}

@keyframes blink {
    0% { background-color: #ffeb3b; } /* Желтый */
    50% { background-color: transparent; }
    100% { background-color: #ffeb3b; }
}

.closed {
    animation: blink 1s infinite; /* 1 секунда, бесконечно */
}
.flex-space {
    display: flex;
    justify-content: space-between;
}
.flex-space button {
    font-size: 14px !important;
}
</style>
