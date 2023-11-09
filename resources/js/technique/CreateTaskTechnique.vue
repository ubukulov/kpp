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
                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="Наименование компании (собственник)"
                                                        v-model="owner"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
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

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="Марка"
                                                        v-model="mark"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="VIN код"
                                                        v-model="vin_code"
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
                disabled: false,
                technique_type_id: null,
                owner: null,
                mark: null,
                vin_code: null,
            }
        },
        methods: {
            createTechniqueTask(){
                this.errors = [];

                if (this.tab === 'tab-2') {
                    this.upload_file = '';

                    if (!this.owner) {
                        this.errors.push('Укажите собственника');
                    }
                    if (!this.technique_type_id) {
                        this.errors.push('Укажите тип технику');
                    }
                    if (!this.mark) {
                        this.errors.push('Укажите марку');
                    }
                    if (!this.vin_code) {
                        this.errors.push('Укажите VIN код');
                    }

                    if (this.errors.length === 0) {
                        this.overlay = true;
                        this.disabled = true;

                        let formData = new FormData();
                        formData.append('task_type', this.task_type);
                        formData.append('trans_type', this.trans_type);
                        formData.append('owner', this.owner);
                        formData.append('mark', this.mark);
                        formData.append('vin_code', this.vin_code);
                        formData.append('technique_type_id', this.technique_type_id);

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

                    if (this.errors.length === 0) {
                        this.overlay = true;
                        this.disabled = true;
                        const config = {
                            headers: { 'content-type': 'multipart/form-data' }
                        };

                        let formData = new FormData();
                        formData.append('task_type', this.task_type);
                        formData.append('trans_type', this.trans_type);
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
                }
            }
        },
    }
</script>

<style scoped>

</style>
