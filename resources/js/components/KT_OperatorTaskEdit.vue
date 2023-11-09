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
                            <label>Документ основание (Скан)</label>
                            <input type="file" ref="document_base" @change="setUploadFile(2)" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-12">
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
                            <button :disabled="disabled" style="float: right;" @click="updateContainerTask()"  name="update_task" type="button" class="btn btn-success">Обновить заявку</button>
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
        props: ['containerTask'],
        data() {
            return {
                overlay: false,
                errors: [],
                task_type: this.containerTask.task_type,
                trans_type: this.containerTask.trans_type,
                document_base: '',
                upload_file: '',
                disabled: false,
                orderAuto: false,
            }
        },
        methods: {
            updateContainerTask(){
                this.errors = [];

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

                    axios.post('/container-terminals/container/'+this.containerTask.id+'/update-container-task', formData, config)
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
        }
    }
</script>

<style scoped>

</style>
