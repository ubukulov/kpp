<template>
    <v-app>
        <v-main>
            <v-container>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Тип документа</label>
                            <select v-model="task_type" class="form-control">
                                <option value="receive">Прием</option>
                                <option value="ship">Выдача</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Тип транспорта</label>
                            <select v-model="trans_type" class="form-control">
                                <option value="train">ЖД</option>
                                <option value="auto">Авто</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Укажите компанию</label>
                            <v-select
                                :items="companies"
                                class="form-control"
                                :hint="`${companies.id}, ${companies.short_en_name}`"
                                item-value="id"
                                v-model="company_id"
                                item-text="short_en_name"
                                @change="getAgreements"
                            ></v-select>
                        </div>
                    </div>

                    <div v-if="task_type == 'ship' && company_id !== null" class="col-md-12">
                        <div class="form-group">
                            <label>Доворенность</label>
                            <v-select
                                :items="agreements"
                                class="form-control"
                                :hint="`${agreements.id}, ${agreements.name}`"
                                item-value="id"
                                v-model="agreement_id"
                                item-text="name"
                            ></v-select>
                            <v-btn
                                class="ma-2"
                                outlined
                                color="indigo"
                                @click="dialog=true"
                            >
                                <v-icon>mdi-plus-circle-outline</v-icon>&nbsp;Создать
                            </v-btn>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <v-card>
                            <v-tabs
                                v-model="tab"
                                fixed-tabs
                            >
                                <v-tabs-slider></v-tabs-slider>
                                <v-tab
                                    href="#tab-1"
                                    class="primary--text"
                                >
                                    <v-icon>mdi-file</v-icon>&nbsp; Импорт из файла
                                </v-tab>

                                <v-tab
                                    href="#tab-2"
                                    class="primary--text"
                                >
                                    <v-icon>mdi-keyboard</v-icon>&nbsp; Ручной
                                </v-tab>
                            </v-tabs>

                            <v-tabs-items v-model="tab">
                                <v-tab-item
                                    :value="'tab-1'"
                                >
                                    <div class="container-fluid" style="margin: 40px 0px;">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label>Файл с переченем контейнеров (Эксель, xls)</label>
                                                    <input type="file" ref="upload_file" @change="setUploadFile(1)" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <a class="btn btn-dark" style="color: #fff;" v-if="task_type==='receive'" href="/files/technique_template.xlsx">
                                                    <v-icon style="color: #fff;">mdi-download</v-icon>&nbsp; Скачать шаблон (прием)
                                                </a>
                                                <a class="btn btn-dark" style="color: #fff;" v-if="task_type === 'ship'" href="/files/technique_template_ship.xlsx">
                                                    <v-icon style="color: #fff;">mdi-download</v-icon>&nbsp; Скачать шаблон (выдачи)
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </v-tab-item>

                                <v-tab-item
                                    :value="'tab-2'"
                                >
                                    <v-container style="margin: 40px 0;">

                                        <v-row>

                                            <v-col v-if="task_type === 'receive'" cols="3">
                                                <div class="form-group">
                                                    <v-select
                                                        label="Тип техники"
                                                        :items="technique_types"
                                                        v-model="technique_type_id"
                                                        :hint="`${technique_types.id}, ${technique_types.name}`"
                                                        item-value="id"
                                                        item-text="name"
                                                        class="form-control"
                                                    ></v-select>
                                                </div>
                                            </v-col>

                                            <v-col v-if="task_type === 'receive'" cols="3">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="Марка"
                                                        v-model="mark"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>

                                            <v-col cols="3">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="VIN код"
                                                        v-model="vin_code"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>

                                            <v-col v-if="task_type === 'receive'" cols="3">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="Цвет"
                                                        v-model="color"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </v-tab-item>
                            </v-tabs-items>
                        </v-card>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <button style="float: left;" onclick="window.location.href = '/container-terminals'" type="button" class="btn btn-primary">Назад</button>
                            <button :disabled="disabled" style="float: right;" @click="createTechniqueTask()"  name="create_task" type="button" class="btn btn-success">Создать заявку</button>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <p v-if="errors.length" style="margin-bottom: 0px !important;">
                            <b>Пожалуйста исправьте указанные ошибки:</b>
                        <ul style="color: red; padding-left: 15px; list-style: circle; text-align: left;">
                            <li v-for="error in errors">{{error}}</li>
                        </ul>
                        </p>
                    </div>
                </div>

                <template>
                    <v-dialog style="z-index: 99999; position: relative; margin-top: 10% !important;" v-model="dialog" persistent max-width="800px">
                        <v-card>
                            <v-card-title>
                                <span class="headline" style="font-size: 40px !important;">Справочник доверенность</span>
                            </v-card-title>
                            <v-card-text>
                                <v-container>
                                    <v-row>

                                        <v-col cols="12">
                                            <v-text-field
                                                label="Названия"
                                                v-model="name"
                                                solo
                                            ></v-text-field>

                                            <v-text-field
                                                label="ФИО водителя"
                                                v-model="full_name"
                                                solo
                                            ></v-text-field>

                                            <v-file-input
                                                label="Скань доверенноста"
                                                v-model="agreement_file"
                                                outlined
                                                dense
                                            ></v-file-input>
                                        </v-col>

                                        <v-col cols="12">
                                            <div class="form-group">
                                                <p v-if="errors.length" style="margin-bottom: 0px !important;">
                                                    <b>Пожалуйста исправьте указанные ошибки:</b>
                                                <ul style="color: red; padding-left: 15px; list-style: circle; text-align: left;">
                                                    <li v-for="error in errors">{{error}}</li>
                                                </ul>
                                                </p>
                                            </div>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="blue darken-1" @click="dialog = false">Отменить</v-btn>
                                <v-btn color="success darken-1" @click="storeAgreement">
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
                </template>

                <v-overlay :value="overlay">
                    <v-progress-circular
                        indeterminate
                        size="64"
                    ></v-progress-circular>
                </v-overlay>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
    import axios from "axios";
    import { Datetime } from 'vue-datetime';
    import 'vue-datetime/dist/vue-datetime.css'

    export default {
        components: {
            datetime: Datetime
        },
        props: [
            'user', 'technique_types'
        ],
        data() {
            return {
                overlay: false,
                errors: [],
                tab: null,
                task_type: 'receive',
                trans_type: 'train',
                upload_file: '',
                document_base: '',
                disabled: false,
                technique_type_id: null,
                color: null,
                mark: null,
                vin_code: null,
                companies: [],
                company_id: null,
                agreements: [],
                agreement_id: null,
                agreement_file: null,
                dialog: false,
                name: null,
                full_name: null
            }
        },
        methods: {
            createTechniqueTask(){
                this.errors = [];

                if (this.tab === 'tab-2') {
                    this.upload_file = '';

                    if (!this.technique_type_id) {
                        this.errors.push('Укажите тип технику');
                    }
                    if (!this.mark) {
                        this.errors.push('Укажите марку');
                    }
                    if (!this.vin_code) {
                        this.errors.push('Укажите VIN код');
                    }
                    if (!this.color) {
                        this.errors.push('Укажите цвет');
                    }
                    if (!this.company_id) {
                        this.errors.push('Укажите компанию');
                    }

                    if (this.errors.length === 0) {
                        this.overlay = true;
                        this.disabled = true;

                        let formData = new FormData();
                        formData.append('task_type', this.task_type);
                        formData.append('trans_type', this.trans_type);
                        formData.append('mark', this.mark);
                        formData.append('color', this.color);
                        formData.append('vin_code', this.vin_code);
                        formData.append('technique_type_id', this.technique_type_id);
                        formData.append('company_id', this.company_id);

                        axios.post('/container-terminals/technique/create-task-by-keyboard', formData)
                            .then(res => {
                                console.log(res);
                                window.location.href = '/container-terminals';
                            })
                            .catch(err => {
                                console.log(err);
                                this.overlay = false;
                                this.errors.push(err.response.data);
                                this.disabled = false;
                            })
                    }
                } else {
                    if (!this.upload_file) {
                        this.errors.push('Укажите файл');
                    }

                    if (!this.company_id) {
                        this.errors.push('Укажите компанию');
                    }

                    if (this.errors.length === 0) {
                        this.overlay = true;
                        this.disabled = true;
                        const config = {
                            headers: { 'content-type': 'multipart/form-data' }
                        };

                        let formData = new FormData();
                        formData.append('task_type', this.task_type);
                        formData.append('trans_type', this.trans_type);
                        formData.append('company_id', this.company_id);
                        formData.append('agreement_id', this.agreement_id);
                        formData.append('upload_file', this.$refs.upload_file.files[0]);

                        axios.post('/container-terminals/technique/create-task-by-file', formData, config)
                            .then(res => {
                                console.log(res);
                                window.location.href = '/container-terminals';
                            })
                            .catch(err => {
                                console.log(err);
                                this.overlay = false;
                                this.errors.push(err.response.data);
                            })
                    }
                }
            },
            setUploadFile(t){
                if (t === 1) {
                    this.upload_file = this.$refs.upload_file.files[0];
                } else {
                    this.document_base = this.$refs.document_base.files[0];
                }
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
            getAgreements(){
                axios.get(`/container-terminals/technique/${this.company_id}/get-agreements`)
                    .then(res => {
                        console.log(res);
                        this.agreements = res.data;
                    })
                    .catch(err => {
                        console.log(err);
                    })
            },
            storeAgreement(){
                let formData = new FormData();
                formData.append('company_id', this.company_id);
                formData.append('name', this.name);
                formData.append('full_name', this.full_name);
                formData.append('agreement_file', this.agreement_file);

                if(!this.name) {
                    this.errors.push('Укажите наименование доверенноста');
                }

                if(!this.full_name) {
                    this.errors.push('Укажите ФИО водителя');
                }

                if (this.errors.length === 0) {
                    this.overlay = true;
                    const config = {
                        headers: { 'content-type': 'multipart/form-data' }
                    };
                    axios.post(`/container-terminals/technique/${this.company_id}/store-agreement`, formData, config)
                        .then(res => {
                            console.log(res);
                            this.getAgreements();
                            this.agreement_id = res.data.id;
                            this.overlay = false;
                            this.dialog = false;
                        })
                        .catch(err => {
                            console.log(err);
                            this.overlay = false;
                        })
                }
            }
        },
        created() {
            this.getTechniqueCompanies();
        }
    }
</script>

<style>
    .v-dialog {
        margin-top: 2% !important;
    }
    .v-btn__content {
        font-size: 14px;
        text-transform: capitalize;
    }
</style>
