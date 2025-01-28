<template>
    <div class="container-fluid">
        <div class="row scango-barcode">
            <div class="col-md-3">
                <div class="scango-phone">
                    <span>Нужна помощь?!<br> Позвоните: 3132</span><br>или: 2055, 2057
                </div>
            </div>

            <div class="col-md-5">
                <div class="scango-barcode" align="center">
                    <img :src="img_path" alt="Штрих-Код накладной" align="center">
                </div>
            </div>

            <div v-if="info" class="col-md-4">
                <p><strong>ФИО:</strong> {{ last_name }}</p>
                <p><strong>Компания:</strong>  {{ company }}</p>
                <p><strong>Подразделение:</strong>  {{ department }}</p>
                <p><strong>Должность:</strong>  {{ position }}</p>
                <p><strong>Номер телефона:</strong>  {{ phone }}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="scango-form" align="center">
                    <label>Отсканируйте <br> QR Code или штрих-код</label>
                    <div><input class="barcode_input" autofocus v-on:keyup.enter="personalControl()" type="text" v-model="barcode"></div>
                </div>

                <div class="form-group">
                    <label></label>
                </div>

                <div v-html="response_html">

                </div>

                <div v-if="progress_circule" class="text-center">
                    <v-progress-circular
                        style="width: 100px; height: 100px;"
                        indeterminate
                        color="primary"
                    ></v-progress-circular>
                </div>

                <v-dialog style="z-index: 99999; position: relative;" v-model="dialog" persistent max-width="800px">
                    <v-card>
                        <v-card-title>
                            <span class="headline" style="font-size: 40px !important;">Личные данные</span>
                            <v-spacer></v-spacer>
                            <v-icon style="cursor: pointer;" @click="dialog=false" large>
                                mdi-close-circle
                            </v-icon>
                        </v-card-title>
                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="4" class="prt_col">
                                        <img style="max-width: 100%;" src="/img/default-user-image.png" alt="">
                                    </v-col>

                                    <v-col cols="8" class="prt_col">
                                        <div>
                                            <p>ФИО:</p> <span>{{ last_name }}</span>
                                        </div>
                                        <div>
                                            <p>Должность:</p> <span>{{ position }}</span>
                                        </div>
                                        <div>
                                            <p>Компания:</p> <span>{{ company }}</span>
                                        </div>
                                    </v-col>

                                    <v-col cols="12">

                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn @click="fixTimeForEmployee(1)" style="padding: 50px;" color="success darken-1" >
                                <v-icon
                                    middle
                                >
                                    mdi-boom-gate-up
                                </v-icon>
                                &nbsp;&nbsp;Вход
                            </v-btn>
                            <v-spacer></v-spacer>
                            <v-btn @click="fixTimeForEmployee(2)" style="padding: 50px;" color="red darken-1">
                                <v-icon
                                    middle
                                >
                                    mdi-boom-gate-down
                                </v-icon>
                                &nbsp;&nbsp;Выход
                            </v-btn>
                            <v-spacer></v-spacer>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    export default {
        data() {
            return {
                barcode: '',
                response_html: '',
                progress_circule: false,
                logs: [],
                dialog: false,
                is_access: false,
                is_failure: false,
                info: false,
                last_name: '',
                position: '',
                department: '',
                phone: '',
                company: '',
                company_long: '',
                user_id: 0,
                img_path: '/img/pc.png',
            }
        },
        methods: {
            personalControl(){
                this.progress_circule = true;
                let formData = new FormData();
                formData.append('barcode', this.barcode);
                axios.post('/scanning-personal-control', formData)
                    .then(res => {
                        this.last_name = res.data.last_name;
                        this.position = res.data.position;
                        this.company = res.data.company;
                        this.department = res.data.department;
                        this.phone = res.data.phone;
                        this.img_path = res.data.avatar;
                        this.barcode = '';
                        this.progress_circule = false;
                        this.info = true;
                        this.response_html = res.data.html;
                    })
                    .catch(err => {
                        if(err.response.status == 403) {
                            this.is_failure = true;
                            this.is_access = false;
                            this.progress_circule = false;
                            this.barcode = '';
                            this.last_name = err.response.data.data.last_name;
                            this.position = err.response.data.data.position;
                            this.company = err.response.data.data.company;
                            this.department = err.response.data.data.department;
                            this.phone = err.response.data.data.phone;
                            this.img_path = err.response.data.data.avatar;
                            this.info = true;
                            this.response_html = err.response.data.data.html;
                        } else if(err.response.status == 404){
                            this.progress_circule = false;
                            this.barcode = '';
                            this.info = false;
                            this.img_path = '/img/pc.png';
                            this.response_html = err.response.data.data.html;
                        }else {
                            console.log(err)
                        }
                    })
            },
            /*fixTimeForEmployee(type) {
                let formData = new FormData();
                formData.append('user_id', this.user_id);
                formData.append('operation_type', type);
                axios.post('/fix-date-time-for-current-user', formData)
                    .then(res => {
                        console.log(res);
                        this.dialog = false;
                        this.response_html = res.data.data;
                        setTimeout(() => this.closeInfoHtml(), 5000);
                    })
                    .catch(err => {
                        console.log(err);
                    })
            },*/
            closeInfoHtml(){
                this.response_html = '';
            }
        },
    }
</script>

<style scoped>
    .scango-phone {
        position: absolute;
        top: 100px;
        left: 100px;
    }
    .scango-phone > span {
        color: red;
        font-size: 20px;
    }
    .scango-barcode {
        background: orange;
    }
    .scango-barcode > img {
        width: 500px;
        height: 260px;
        border: dotted;
    }
    label {
        font-size: 40px;
        font-weight: 700;
        line-height: 40px;
    }
    input[type="text"] {
        padding: 5px;
    }
    .barcode_input {
        width: 600px;
        height: 40px;
        font-size: 30px;
        font-family: Arial Black;
        color: blue;
        border-color: #212529;
        border: 2px solid #212529;
        border-radius: 4px;
    }
    .v-progress-circular {
        margin: 1rem;
    }
</style>
