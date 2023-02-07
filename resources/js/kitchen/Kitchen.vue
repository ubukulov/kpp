<template>
    <v-app>
        <v-main>
            <v-container>
                <v-row>
                    <v-col cols="4">
                        <div v-if="info" v-html="infoHtml"></div>
                        <div class="form-group">
                            <label>Отсканируйте или введите ID пользователя</label><br>
                            <textarea onfocus="this.value=''" @keyup.enter="checkScanCode()" ref="username" id="username" v-model="username" cols="40" rows="4" style="border:1px solid red;"></textarea>
                        </div>

                        <div class="form-group">
                            <button type="button" @click="checkScanCode()" class="btn btn-success nicebtn2">Внести</button>
                        </div>
                    </v-col>

                    <v-col cols="8">
                        <div v-show="isCameraOpen && isLoading" class="camera-loading">
                            <ul class="loader-circle">
                                <li></li>
                                <li></li>
                                <li></li>
                            </ul>
                        </div>

                        <div v-if="isCameraOpen" v-show="!isLoading" class="camera-box" :class="{ 'flash' : isShotPhoto }">

                            <div class="camera-shutter" :class="{'flash' : isShotPhoto}"></div>

                            <video v-show="!isPhotoTaken" ref="camera" :width="1280" :height="720" autoplay></video>

                            <canvas v-show="isPhotoTaken" id="photoTaken" ref="canvas" :width="1280" :height="720"></canvas>
                        </div>

                        <!--<div v-if="isCameraOpen && !isLoading" class="camera-shoot">
                            <button type="button" class="button" @click="takePhoto">
                                <img src="https://img.icons8.com/material-outlined/50/000000/camera&#45;&#45;v2.png">
                            </button>
                        </div>-->

                        <!--<div v-if="isPhotoTaken && isCameraOpen" class="camera-download">
                            <a id="downloadPhoto" download="my-photo.jpg" class="button" role="button" @click="downloadImage">
                                Download
                            </a>
                        </div>-->
                    </v-col>
                </v-row>


                <v-dialog v-model="dialog"  max-width="700px">

                    <v-card>
                        <v-card-title>
                            <span class="headline">Выберите тип обеда</span>
                        </v-card-title>
                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="5">
                                        <img v-if="user.image !== null" :src="user.image" width="280" alt="">
                                        <img v-else src="/img/default-user-image.png" width="280" alt="">
                                    </v-col>
                                    <v-col cols="7">
                                        <div id="userinfo">
                                            <ul>
                                                <li>Ф.И.О: <strong id="as_fio">{{fullname}}</strong></li>
                                                <li>Компания: <strong id="as_com">{{company_name}}</strong></li>
                                                <li>Обедов на сегодня: <strong id="as_cnt">{{count}}</strong></li>
                                            </ul>
                                        </div>
                                    </v-col>

                                    <v-col cols="12">
                                        <div class="content_mw" v-if="user.count !== 3">
                                            <v-btn color="success" :disabled="isDisable1" @click="addAshana(1)">Стандарт</v-btn>
                                            <v-btn color="success" :disabled="isDisable2" @click="addAshana(2)">Булочки</v-btn>
                                        </div>

                                        <div class="content_mw" v-if="user.count === 2">
                                            <h2 style="font-size: 30px; color: red;"><strong>Ваш лимит обедов за сегодня исчерпан</strong></h2>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" @click="cancelOperation()">Отменить</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
    import axios from 'axios';
    export default {
        data(){
            return {
                dialog: false,
                info: false,
                infoHtml: "",
                username: "",
                fullname: "",
                company_name: "",
                count: 0,
                user_id: 0,
                isDisable1: false,
                isDisable2: false,
                search: '',
                user: [],
                headers: [
                    {
                        text: 'Наименование',
                        align: 'start',
                        filterable: false,
                        value: 'short_ru_name',
                    },
                    { text: 'Стандарт', value: 'stan' },
                    { text: 'Булочки', value: 'bul' },
                    { text: 'Общий', value: 'sum' },
                ],
                items: {},
                options: [
                    {
                        id: 1,
                        text: 'За сегодня'
                    },
                    {
                        id: 2,
                        text: 'За вчера'
                    },
                    {
                        id: 3,
                        text: 'За неделю'
                    },
                ],
                option_id: 1,
                isCameraOpen: false,
                isPhotoTaken: false,
                isShotPhoto: false,
                isLoading: false,
                userImage: ''
            }
        },
        props: [
            'cashier'
        ],
        methods: {
            cancelOperation(){
                this.dialog = false;
                this.username = "";
                setTimeout(function() {
                    document.getElementById("username").focus();
                }, 0);
                this.isDisable1 = false;
                this.isDisable2 = false;
                this.isCameraOpen = true;
                this.isPhotoTaken = false;
            },
            addAshana(type){
                if(type === 1) {
                    this.isDisable1 = true;
                } else {
                    this.isDisable2 = true;
                }
                let formData = new FormData();
                const config = {
                    headers: { 'content-type': 'multipart/form-data' }
                };
                formData.append('din_type', type);
                formData.append('user_id', this.user_id);
                formData.append('cashier_id', this.cashier.id);
                formData.append('path_to_image', this.userImage);
                axios.post('ashana/fix-changes', formData, config)
                    .then(res => {
                        this.info = true;
                        let din_name = res.data.din_type === 1 ? 'Обед Стандарт' : 'Булочки';
                        this.infoHtml = '<h2 style="color: green; font-size: 30px;">1 <strong>' + din_name + '</strong> записан на <strong>';
                        this.infoHtml = this.infoHtml + res.data.full_name + '</strong></h2></br><h2 style="color: green; font-size: 30px;">Талонов за сегодня: <strong>' + res.data.count + '</h2>';
                        this.dialog = false;
                        this.isDisable1 = false;
                        this.isDisable2 = false;
                        this.getStatisticsForOption();
                        this.isCameraOpen = true;
                        this.isPhotoTaken = false;
                    })
                    .catch(err => {
                        console.log(err)
                    });
                this.username = "";
                setTimeout(function() {
                    document.getElementById("username").focus();
                }, 0);
            },
            getStatisticsForOption(){
                axios.get(`ashana/get-statistics/${this.option_id}`)
                    .then(res => {
                        console.log(res.data);
                        this.items = res.data;
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            checkScanCode(){
                let formData = new FormData();
                let scan_code = this.username;
                formData.append('username', scan_code);
                this.infoHtml = '';
                if (scan_code !== '') {
                    axios.post('ashana/get-user-info', formData)
                        .then(res => {
                            let data = res.data;
                            this.user = data;
                            this.fullname = data.full_name;
                            this.company_name = data.company_name;
                            this.count = data.count;
                            this.user_id = data.user_id;
                            this.dialog = true;
                            this.takePhoto();
                        })
                        .catch(err => {
                            console.log(err);
                            if(err.response.status === 404) {
                                this.info = true;
                                this.infoHtml = '<strong style="font-size: 30px; color: red;">' + this.username +'  - не найден</strong>';
                            }
                            if(err.response.status === 406) {
                                this.info = true;
                                this.infoHtml = '<strong style="font-size: 30px; color: red;">' + err.response.data.message +'</strong>';
                            }
                        })
                } else {
                    this.dialog = false
                }
            },
            createCameraElement() {
                this.isLoading = true;
                const constraints = (window.constraints = {
                    audio: false,
                    video: true
                });
                navigator.mediaDevices
                    .getUserMedia(constraints)
                    .then(stream => {
                        this.isLoading = false;
                        this.$refs.camera.srcObject = stream;
                    })
                    .catch(error => {
                        this.isLoading = false;
                        alert("May the browser didn't support or there is some errors.");
                    });
            },

            stopCameraStream() {
                let tracks = this.$refs.camera.srcObject.getTracks();

                tracks.forEach(track => {
                    track.stop();
                });
            },

            takePhoto() {
                if(!this.isPhotoTaken) {
                    this.isShotPhoto = true;

                    const FLASH_TIMEOUT = 50;

                    setTimeout(() => {
                        this.isShotPhoto = false;
                    }, FLASH_TIMEOUT);
                }

                this.isPhotoTaken = !this.isPhotoTaken;

                const context = this.$refs.canvas.getContext('2d');
                context.drawImage(this.$refs.camera, 0, 0, 1280, 720);
                this.userImage = this.$refs.canvas.toDataURL("image/jpeg");
            },
        },
        mounted(){
            this.$refs.username.focus();
            this.isCameraOpen = true;
            this.createCameraElement();
        },
        created(){
            this.items = this.getStatisticsForOption();
        }
    }
</script>

<style scoped>
    .nicebtn2 {
        background: #9C6;
        font-weight: bold;
        color: #DA4C3C;
        padding: 6px 10px 6px 10px;
        display: inline-block;
        margin-right: 10px;
    }
    .close {
        display: none;
    }
    #panel, #header,.menu-items-body,.menu-items-header,#bx-im-bar {
        display: none;
    }
    #boxes #dialog {
        width: 475px;
        height: 303px;
        padding: 10px;
        background-color: #ffffff;
    }
    #boxes .window {
        position: absolute;
        left: 0;
        top: 0px;
        -top: 40px;
        width: 440px;
        height: 200px;
        display: none;
        z-index: 9999;
        padding: 20px;
        overflow: hidden;
    }
    #userinfo strong {
        color: #000;
    }
    #userinfo ul {
        list-style: none;
    }
    strong {
        font-weight: bold;
    }
    .green_h2 {
        color: green;
        font: 300 32px/36px 'arian_amuregular', sans-serif;
    }
    .bigerror {
        font-size: 30px;
        color: red;
    }
    .theme--light.v-card {
        position: absolute;
        width: 700px;
        max-width: 100%;
        top: 50px;
        left: 50px;
    }
</style>
