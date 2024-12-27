<template>
    <v-dialog style="z-index: 99999; position: relative; margin-top: 2% !important;" v-model="isModalVisible" persistent max-width="1000px">
        <v-card>
            <v-card-title>
                <span class="headline" style="font-size: 40px !important;">Окно корешок</span>
            </v-card-title>
            <v-card-text>
                <v-container>
                    <v-row>
                        <v-col cols="6">
                            <v-select
                                :items="companies"
                                :hint="`${companies.id}, ${companies.short_en_name}`"
                                item-value="id"
                                v-model="company_id"
                                item-text="short_en_name"
                                outlined
                                dense
                                label="Выберите клиента"
                            ></v-select>
                        </v-col>

                        <v-col cols="6">
                            <v-select
                                v-if="company_id"
                                :items="types"
                                v-model="type"
                                :hint="`${types.value}, ${types.text}`"
                                item-value="value"
                                item-text="text"
                                outlined
                                dense
                                label="Укажите тип"
                                @change="getItems()"
                            ></v-select>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-card flat>
                            <v-card-title class="d-flex align-center pe-2">
                                <v-icon icon="mdi-video-input-component"></v-icon> &nbsp;
                                Список винкодов

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

                            <v-divider></v-divider>
                            <v-data-table
                                :headers="code_headers"
                                :items="codes"
                                :items-per-page="8"
                                class="elevation-1"
                                show-select
                                :search="search"
                                v-model="selectedCodes"
                            ></v-data-table>
                        </v-card>
                    </v-row>

                    <v-row v-if="type" class="mt-3">
                        <v-col cols="5">
                            <v-text-field
                                label="Номер автомашины"
                                v-model="car_number"
                                outlined
                            ></v-text-field>
                        </v-col>

                        <v-col cols="5">
                            <v-text-field
                                label="ФИО водителя"
                                v-model="driver_name"
                                outlined
                            ></v-text-field>
                        </v-col>

                        <v-col cols="2">
                            <v-text-field class="mt-2" label="Выбрано" disabled :value="selectedCodes.length"></v-text-field>
                        </v-col>
                    </v-row>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" @click="hideModal">Отменить или закрыть окно</v-btn>
                <v-btn :disabled="selectedCodes && selectedCodes.length === 0" @click="saveAndPreviewSpine" color="success darken-1">
                    <v-icon
                        middle
                    >
                        mdi-save
                    </v-icon>
                    &nbsp;Сохранить и просмотр
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    import { mapGetters, mapActions } from 'vuex';
    import axios from "axios";
    export default {
        data() {
            return {
                companies: [],
                company_id: null,
                car_number: null,
                driver_name: null,
                types: [
                    {
                        'value': 'receive',
                        'text' : 'Размещение'
                    },
                    {
                        'value': 'ship',
                        'text' : 'Выдача'
                    }
                ],
                type: null,
                codes: [],
                code_headers: [
                    {
                        text: 'ID',
                        align: 'start',
                        sortable: true,
                        value: 'id',
                    },
                    { text: 'ВИНКОД', value: 'code'},
                ],
                search: '',
                selectedCodes: []
            }
        },
        computed: {
            ...mapGetters(['isModalVisible'])
        },
        methods: {
            //...mapActions(['hideModal']),
            hideModal() {
                this.codes = [];
                this.company_id = null;
                this.type = null;
                this.$store.dispatch('hideModal');
            },
            getTechniqueCompanies(){
                axios.get('/container-terminals/get-technique-companies')
                    .then(res => {
                        console.log(res);
                        this.companies = res.data;
                    })
                    .catch(err => {
                        console.log(err);
                    })
            },
            saveAndPreviewSpine(){
                let formData = new FormData();
                formData.append('company_id', this.company_id);
                formData.append('type', this.type);
                formData.append('car_number', this.car_number);
                formData.append('driver_name', this.driver_name);
                if(this.selectedCodes && this.selectedCodes.length > 0) {
                    formData.append('selectedCodes', JSON.stringify(this.selectedCodes));
                }

                axios.post('/container-terminals/technique/spine', formData)
                .then(res => {
                    console.log(res);
                    this.company_id = null;
                    this.type = null;
                    this.name = null;
                    this.car_number = null;
                    this.driver_name = null;
                    this.codes = null;
                    this.selectedCodes = [];
                    this.getItems();
                    this.hideModal();
                    let url = `/container-terminals/technique/${res.data.id}/print`;
                    window.open(url, "_blank");
                })
                .catch(err => {
                    console.log(err);
                })
            },
            getItems(){
                let formData = new FormData();
                formData.append('company_id', this.company_id);
                formData.append('type', this.type);

                axios.post('/container-terminals/technique/get-spine-vincodes', formData)
                    .then(res => {
                        console.log(res);
                        this.codes = res.data;
                    })
                    .catch(err => {
                        console.log(err);
                    })
            }
        },
        created() {
            this.getTechniqueCompanies();
        }
    }
</script>

<style scoped>
.v-dialog>.v-card>.v-card__subtitle, .v-dialog>.v-card>.v-card__text {
    padding: 0 14px 10px;
}
</style>
