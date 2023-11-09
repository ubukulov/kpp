<template>
    <v-app>
        <v-main>
            <v-container>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Тип документа</label>
                            <select v-if="user.company_id !== 2" v-model="task_type" class="form-control">
                                <option value="receive">Прием</option>
                                <option value="ship">Выдача</option>
                            </select>

                            <select disabled v-if="user.company_id === 2" v-model="task_type" class="form-control">
                                <option value="ship">Выдача</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Тип транспорта</label>
                            <select v-if="user.company_id !== 2" v-model="trans_type" class="form-control">
                                <option value="train">ЖД</option>
                                <option value="auto">Авто</option>
                            </select>

                            <select disabled v-if="user.company_id === 2" v-model="trans_type" class="form-control">
                                <option value="auto">Авто</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Документ основание (Скан) не обязательно</label>
                            <input type="file" ref="document_base" @change="setUploadFile(2)" class="form-control">
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
                                                <a class="btn btn-dark" style="color: #fff;" v-if="task_type==='receive'" href="/files/kt_template.xlsx">
                                                    <v-icon style="color: #fff;">mdi-download</v-icon>&nbsp; Скачать шаблон (прием)
                                                </a>
                                                <a class="btn btn-dark" style="color: #fff;" v-if="task_type === 'ship'" href="/files/kt_template_ship.xlsx">
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
                                                        label="Номер контейнера"
                                                        v-model="container_number"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="Клиент"
                                                        v-model="company"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="Номер вагона/машины"
                                                        v-model="car_number_carriage"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-select
                                                        label="Тип контейнера"
                                                        :items="types"
                                                        v-model="container_type"
                                                        class="form-control"
                                                    ></v-select>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-select
                                                        label="Статус"
                                                        :items="states"
                                                        v-model="state"
                                                        class="form-control"
                                                    ></v-select>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-select
                                                        label="Растаможен"
                                                        :items="customs"
                                                        v-model="custom"
                                                        class="form-control"
                                                    ></v-select>
                                                </div>
                                            </v-col>



                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="Номер пломбы по документам"
                                                        v-model="seal_number_document"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="Номер пломбы по факту"
                                                        v-model="seal_number_fact"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="Контрагент"
                                                        v-model="contractor"
                                                        hide-details="auto"
                                                    ></v-text-field>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <label>Дата/время подачи</label>
                                                    <datetime v-model="datetime_submission" input-style="font-size: 22px !important;color: rgba(0,0,0,.87);border: 1px solid #ccc;padding: 2px;" input-class="" type="datetime" format="yyyy-MM-dd HH:mm" value-zone="Asia/Almaty" :phrases="{ok: 'ОК', cancel: 'Отмена'}"></datetime>
                                                </div>
                                            </v-col>

                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <label>Дата/время прибытие</label>
                                                    <datetime v-model="datetime_arrival" input-style="font-size: 22px !important;color: rgba(0,0,0,.87);border: 1px solid #ccc;padding: 2px;" input-class="" type="datetime" format="yyyy-MM-dd HH:mm" value-zone="Asia/Almaty" :phrases="{ok: 'ОК', cancel: 'Отмена'}"></datetime>
                                                </div>
                                            </v-col>



                                            <v-col cols="4">
                                                <div class="form-group">
                                                    <v-text-field
                                                        label="Примечание"
                                                        v-model="note"
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

                    <div v-if="task_type === 'ship' && trans_type === 'auto'" class="col-md-12">
                        <v-checkbox
                            v-model="orderAuto"
                            label="Заявка на прием обратно (Автоматический)"
                        ></v-checkbox>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <button style="float: left;" onclick="window.location.href = '/container-terminals'" type="button" class="btn btn-primary">Назад</button>
                            <button :disabled="disabled" style="float: right;" @click="createContainerTask()"  name="create_task" type="button" class="btn btn-success">Создать заявку</button>
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
            'user'
        ],
        data() {
            return {
                overlay: false,
                errors: [],
                tab: null,
                task_type: 'receive',
                trans_type: 'train',
                document_base: '',
                upload_file: '',
                disabled: false,
                types: [40, 45, 20],
                states: ['Груженый', 'Порожний'],
                customs: ['Да', 'Нет'],
                container_number: '',
                company: '',
                car_number_carriage: null,
                seal_number_document: null,
                seal_number_fact: null,
                note: null,
                datetime_submission: null,
                datetime_arrival: null,
                contractor: null,
                state: '',
                custom: null,
                container_type: '',
                orderAuto: false,
            }
        },
        methods: {
            createContainerTask(){
                this.errors = [];

                if (this.tab === 'tab-2') {
                    this.upload_file = '';
                    if (!this.container_number) {
                        this.errors.push('Укажите номер контейнера');
                    }
                    if (!this.company) {
                        this.errors.push('Укажите клиента');
                    }
                    if (!this.car_number_carriage) {
                        this.errors.push('Укажите номер вагона/машины');
                    }
                    if (!this.container_type) {
                        this.errors.push('Укажите тип контейнера');
                    }
                    if (!this.state) {
                        this.errors.push('Укажите статус');
                    }
                    /*if (!this.custom) {
                        this.errors.push('Контейнер растаможено?');
                    }
                    if (!this.datetime_submission) {
                        this.errors.push('Укажите Дата/время подачи');
                    }
                    if (!this.datetime_arrival) {
                        this.errors.push('Укажите Дата/время прибытие');
                    }*/

                    if (this.errors.length === 0) {
                        this.overlay = true;
                        this.disabled = true;

                        let formData = new FormData();
                        formData.append('task_type', this.task_type);
                        formData.append('trans_type', this.trans_type);
                        formData.append('container_number', this.container_number);
                        formData.append('company', this.company);
                        formData.append('car_number_carriage', this.car_number_carriage);
                        formData.append('container_type', this.container_type);
                        formData.append('state', this.state);
                        formData.append('custom', this.custom);
                        formData.append('seal_number_document', this.seal_number_document);
                        formData.append('seal_number_fact', this.seal_number_fact);
                        formData.append('contractor', this.contractor);
                        formData.append('datetime_submission', this.datetime_submission);
                        formData.append('datetime_arrival', this.datetime_arrival);
                        formData.append('note', this.note);

                        axios.post('/container-terminals/container/receive-container-by-keyboard', formData)
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
                        this.errors.push('Укажите файл с переченем контейнеров');
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
                        formData.append('document_base', this.$refs.document_base.files[0]);
                        formData.append('upload_file', this.$refs.upload_file.files[0]);
                        formData.append('order_auto', this.orderAuto);

                        axios.post('/container-terminals/container/receive-container-by-operator', formData, config)
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
            }
        },
        created() {
            this.file_path = this.file_receive;
            let company_id = this.user.company_id;
            this.task_type  = company_id !== 2 ? 'receive' : 'ship';
            this.trans_type = company_id !== 2 ? 'train' : 'auto';
            this.orderAuto  = company_id !== 2 ? false : true;
        }
    }
</script>

<style scoped>

</style>
