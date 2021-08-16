<template>
    <v-app>
        <v-main>
            <v-container>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Номер документа</label>
                            <input type="text" v-model="title" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Тип документа</label>
                            <select v-model="task_type" class="form-control">
                                <option value="receive">Прием</option>
                                <option value="ship">Выдача</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Тип транспорта</label>
                            <select v-model="trans_type" class="form-control">
                                <option value="train">ЖД</option>
                                <option value="auto">Авто</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Документ основание (Скан)</label>
                            <input type="file" ref="document_base" @change="setUploadFile(2)" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Файл с переченем контейнеров (Эксель, xls)</label>
                            <input type="file" ref="upload_file" @change="setUploadFile(1)" class="form-control">
                        </div>

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

    export default {
        data() {
            return {
                overlay: false,
                errors: [],
                title: '',
                task_type: 'receive',
                trans_type: 'train',
                document_base: '',
                upload_file: '',
                disabled: false
            }
        },
        methods: {
            createContainerTask(){
                this.errors = [];
                if (!this.title) {
                    this.errors.push('Укажите номер заявки');
                }

                if (!this.upload_file) {
                    this.errors.push('Укажите файл с переченем контейнеров');
                }

                if (this.errors.length == 0) {
                    this.overlay = true;
                    this.disabled = true;
                    const config = {
                        headers: { 'content-type': 'multipart/form-data' }
                    };

                    let formData = new FormData();
                    formData.append('title', this.title);
                    formData.append('task_type', this.task_type);
                    formData.append('trans_type', this.trans_type);
                    formData.append('document_base', this.$refs.document_base.files[0]);
                    formData.append('upload_file', this.$refs.upload_file.files[0]);

                    axios.post('/container-terminals/container/receive-container-by-operator', formData, config)
                    .then(res => {
                        console.log(res);
                        this.overlay = false;
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
        }
    }
</script>

<style scoped>

</style>
