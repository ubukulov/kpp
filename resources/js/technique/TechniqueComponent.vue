<template>
    <v-app>
        <v-main>
            <v-container>
                <v-card
                    max-width="400"
                    class="mx-auto"
                >
                    <v-container>
                        <v-row dense>
                            <v-col cols="12">
                                <v-container>
                                    <v-row style="background: whitesmoke;padding: 10px;">
                                        <div style="width: 80%">
                                            {{ name }}
                                        </div>
                                        <div style="width: 20%;">
                                <span style="float: right; font-size: 14px;">
                                    <a href="/logout">Выйти</a>
                                </span>
                                        </div>
                                    </v-row>
                                </v-container>
                            </v-col>

                            <v-col
                                class="d-flex mb-3"
                                cols="12"
                                sm="6"
                                v-if="bottom_nav ==='tasks'"
                            >

                            </v-col>

                            <v-row v-if="bottom_nav ==='tasks'">
                                <v-col cols="9">
                                    <v-text-field
                                        v-model="vin_code"
                                        label="Введите VIN код"
                                        hide-details="auto"
                                        class="vin_code_input"
                                    ></v-text-field>
                                </v-col>

                                <v-col cols="3">
                                    <v-btn
                                        depressed
                                        color="primary"
                                        @click="startQR()"
                                    >
                                        <v-icon>mdi-qrcode-scan</v-icon>
                                    </v-btn>
                                </v-col>

                                <v-col cols="12" v-if="scan_qr">
                                    <StreamBarcodeReader @decode="onDecode" @loaded="onLoaded"></StreamBarcodeReader>
                                </v-col>

                                <v-divider></v-divider>

                                <v-col cols="12">
                                    <div v-if="info_container != ''" style="font-size: 20px;
                        line-height: 20px;
                        margin-bottom: 30px;
                        font-weight: bold;
                        border: 1px solid yellowgreen;
                        padding: 10px;" v-html="info_container">

                                    </div>
                                </v-col>

                                <v-container>
                                    <v-row v-if="buttons">
                                        <v-col cols="12">
                                            <button @click="receiveStep()" style="width: 100%;" :disabled="is_receive" :class="[is_receive == true ? 'btn-light' : 'btn-success']" type="button" class="btn">Размещение</button>
                                        </v-col>
                                        <v-col cols="12">
                                            <button @click="shipTechnique()" style="width: 100%;" type="button" :disabled="is_shipping" class="btn" :class="[is_shipping == true ? 'btn-light' : 'is_shipping']">Выдача</button>
                                        </v-col>
                                        <v-col cols="12">
                                            <button @click="moveStep()" style="width: 100%;" type="button" :disabled="is_moving" class="btn" :class="[is_moving == true ? 'btn-light' : 'is_moving']">Перемещение</button>
                                        </v-col>
                                    </v-row>

                                    <v-row v-if="receive_step === 2">
                                        <v-col cols="12">
                                            <v-select
                                                label="Укажите зону"
                                                :items="technique_places"
                                                outlined
                                                :hint="`${technique_places.id}, ${technique_places.name}`"
                                                item-value="id"
                                                v-model="technique_place_id"
                                                item-text="name"
                                            ></v-select>
                                        </v-col>

                                        <v-col cols="12">
                                            <v-checkbox @click="toggleCamera" label="дефекты" v-model="defect"></v-checkbox>
                                        </v-col>

                                        <v-col v-if="defect" cols="12">
                                            <v-textarea v-model="defect_note" label="Описание дефекта" variant="solo"></v-textarea>
                                        </v-col>



                                        <v-col v-if="defect" cols="12">
                                            <p>Сделаете фото</p>
                                            <div v-if="isCameraOpen" v-show="!isLoading" class="camera-box" :class="{ 'flash' : isShotPhoto }">

                                                <div class="camera-shutter" :class="{'flash' : isShotPhoto}"></div>

                                                <video v-show="!isPhotoTaken" ref="camera" style="width: 100%;" :height="337.5" autoplay></video>

                                                <canvas v-show="isPhotoTaken" id="photoTaken" ref="canvas" style="width: 100%;" :height="337.5"></canvas>
                                            </div>

                                            <div style="text-align: center;" v-if="isCameraOpen && !isLoading" class="camera-shoot">
                                                <button type="button" class="button" @click="takePhoto">
                                                    <img src="https://img.icons8.com/material-outlined/50/000000/camera--v2.png">
                                                </button>
                                            </div>

                                            <div v-if="isPhotoTaken && isCameraOpen" class="camera-download">
                                                <a id="downloadPhoto" download="my-photo.jpg" class="button" role="button" @click="downloadImage">
                                                    Download
                                                </a>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" class="div-buttons">
                                            <button @click="receiveStep()" type="button" class="btn btn-light">Назад</button>
                                            <button @click="receiveTechnique()" type="button" class="btn btn-success">Разместить</button>
                                        </v-col>
                                    </v-row>

                                    <v-row v-if="move_step === 2">
                                        <v-col cols="12">
                                            <v-select
                                                label="Текущая зона"
                                                :items="technique_places"
                                                outlined
                                                disabled
                                                :hint="`${technique_places.id}, ${technique_places.name}`"
                                                item-value="id"
                                                v-model="technique_place_now_id"
                                                item-text="name"
                                            ></v-select>
                                        </v-col>

                                        <v-col cols="12">
                                            <v-select
                                                label="Укажите зону"
                                                :items="technique_places"
                                                outlined
                                                :hint="`${technique_places.id}, ${technique_places.name}`"
                                                item-value="id"
                                                v-model="technique_place_id"
                                                item-text="name"
                                            ></v-select>
                                        </v-col>

                                        <v-col cols="4">
                                            <button @click="moveStep()" type="button" class="btn btn-light">Назад</button>
                                        </v-col>
                                        <v-col cols="6">
                                            <button @click="moveTechnique()" type="button" class="btn btn-success">Переместить</button>
                                        </v-col>
                                    </v-row>
                                </v-container>


                            </v-row>

                            <v-row class="mt-2" v-if="bottom_nav ==='search'">

                            </v-row>
                        </v-row>

                        <v-overlay :value="overlay">
                            <v-progress-circular
                                indeterminate
                                size="64"
                            ></v-progress-circular>
                        </v-overlay>
                    </v-container>
                </v-card>
            </v-container>
        </v-main>

        <v-footer app>
            <v-bottom-navigation v-model="bottom_nav">
                <v-btn value="tasks">
                    <span style="font-size: 12px;">Операции</span>

                    <v-icon>mdi-history</v-icon>
                </v-btn>

                <v-btn value="search">
                    <span style="font-size: 12px;">Поиск</span>

                    <v-icon>mdi-search-web</v-icon>
                </v-btn>

                <v-btn value="nearby">
                    <span style="font-size: 12px;">Статистика</span>

                    <v-icon>mdi-margin</v-icon>
                </v-btn>
            </v-bottom-navigation>
        </v-footer>
    </v-app>
</template>

<script>
    import axios from "axios";
    import { StreamBarcodeReader } from "vue-barcode-reader";

    export default {
        components: {
            StreamBarcodeReader
        },
        data: () => ({
            overlay: false,
            bottom_nav: 'tasks',
            vin_code: null,
            scan_qr: false,
            info_container: '',
            system: {
                success: false,
                error: false,
            },
            camera: 'auto',
            result: null,
            showScanConfirmation: false,
            is_receive: true,
            is_shipping: true,
            is_moving: true,
            technique_places: [],
            technique_place_id: null,
            technique_place_now_id: null,
            receive_step: 1,
            move_step: 1,
            buttons: true,
            isCameraOpen: false,
            isPhotoTaken: false,
            isShotPhoto: false,
            isLoading: false,
            link: '#',
            defect: false,
            defect_note: ''
        }),
        props: ['name', 'token'],
        methods: {
            startQR(){
                if(!this.vin_code) {
                    this.scan_qr = true;
                } else {
                    this.getInformationByQRCode();
                }
            },
            getInformationByQRCode(){
                let formData = new FormData();
                const config = {
                    headers:{
                        Authorization: "Bearer " + this.token,
                    }
                };

                formData.append('vin_code', this.vin_code);
                axios.post('/api/technique/get-information-by-qr-code', formData, config)
                .then(res => {
                    console.log(res);
                    this.info_container = res.data.message;
                    this.vin_code = res.data.vin_code;
                    if (res.data.event === 1) {
                        this.is_receive = false;
                        this.is_shipping = true;
                        this.is_moving = true;
                    }
                    if (res.data.event === 2) {
                        this.is_receive = true;
                        this.is_shipping = true;
                        this.is_moving = false;
                        this.technique_place_now_id = res.data.technique_stock.technique_place_id;
                    }
                    if (res.data.event === 3) {
                        this.is_receive = true;
                        this.is_shipping = false;
                        this.is_moving = true;
                    }
                })
                .catch(err => {
                    if(err.response.status === 404 || err.response.status === 403) {
                        this.info_container = err.response.data.message;
                        this.is_receive = true;
                        this.is_shipping = true;
                        this.is_moving = true;
                        this.overlay = false;
                    }
                })
            },
            getTechniquePlaces(){
                this.overlay = true;
                const config = {
                    headers:{
                        Authorization: "Bearer " + this.token,
                    }
                };

                axios.get('/api/technique/get-technique-places', config)
                .then(res => {
                    this.technique_places = res.data;
                    this.overlay = false;
                })
                .catch(err => {
                    console.log(err);
                    this.overlay = false;
                })
            },
            receiveTechnique(){
                this.overlay = true;
                let formData = new FormData();
                const config = {
                    headers:{
                        Authorization: "Bearer " + this.token,
                    }
                };

                formData.append('technique_place_id', this.technique_place_id);
                formData.append('vin_code', this.vin_code);
                if(this.defect) {
                    const canvas = document.getElementById("photoTaken").toDataURL("image/jpeg")
                        .replace("image/jpeg", "image/octet-stream");
                    formData.append('image64', canvas);
                    formData.append('defect', this.defect);
                    formData.append('defect_note', this.defect_note);
                }

                axios.post('/api/technique/receive-technique-to-place', formData, config)
                .then(res => {
                    console.log(res);
                    this.overlay = false;
                    this.receive_step = 1;
                    this.buttons = true;
                    this.vin_code = null;
                    this.info_container = res.data.message;
                    this.is_receive = true;
                    this.is_shipping = true;
                    this.is_moving = true;
                })
                .catch(err => {
                    console.log(err);
                    this.overlay = false;
                })
            },
            receiveStep(){
                if(this.receive_step > 1) {
                    this.buttons = true;
                    this.receive_step = 1;
                } else {
                    this.buttons = false;
                    this.receive_step++;
                }
            },
            moveStep() {
                if (this.move_step > 1) {
                    this.buttons = true;
                    this.move_step = 1;
                } else {
                    this.buttons = false;
                    this.move_step++;
                }
            },
            moveTechnique(){
                this.overlay = true;
                let formData = new FormData();
                const config = {
                    headers:{
                        Authorization: "Bearer " + this.token,
                    }
                };

                formData.append('technique_place_current_id', this.technique_place_now_id);
                formData.append('technique_place_id', this.technique_place_id);
                formData.append('vin_code', this.vin_code);
                axios.post('/api/technique/receive-technique-to-place', formData, config)
                    .then(res => {
                        console.log(res);
                        this.overlay = false;
                        this.move_step = 1;
                        this.buttons = true;
                        this.getInformationByQRCode();
                    })
                    .catch(err => {
                        console.log(err);
                        this.overlay = false;
                    })
            },
            shipTechnique(){
                this.overlay = true;
                let formData = new FormData();
                const config = {
                    headers:{
                        Authorization: "Bearer " + this.token,
                    }
                };
                formData.append('vin_code', this.vin_code);
                axios.post('/api/technique/shipping-technique', formData, config)
                    .then(res => {
                        console.log(res);
                        this.overlay = false;
                        this.vin_code = null;
                        this.info_container = res.data.message;
                        this.is_receive = true;
                        this.is_shipping = true;
                        this.is_moving = true;
                    })
                    .catch(err => {
                        console.log(err);
                        this.overlay = false;
                    })
            },
            onDecode(text) {
                console.log(`Decode text from QR code is ${text}`);
                this.vin_code = text;
                this.getInformationByQRCode();
                this.scan_qr = false;
            },
            onLoaded() {
                console.log(`Ready to start scanning barcodes`)
            },
            toggleCamera() {
                if(this.isCameraOpen) {
                    this.isCameraOpen = false;
                    this.isPhotoTaken = false;
                    this.isShotPhoto = false;
                    this.stopCameraStream();
                } else {
                    this.isCameraOpen = true;
                    this.createCameraElement();
                }
            },

            createCameraElement() {
                this.overlay = true;

                const constraints = (window.constraints = {
                    audio: false,
                    video: true
                });


                navigator.mediaDevices
                    .getUserMedia(constraints)
                    .then(stream => {
                        this.overlay = false;
                        this.$refs.camera.srcObject = stream;
                    })
                    .catch(error => {
                        this.overlay = false;
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
                context.drawImage(this.$refs.camera, 0, 0, 450, 337.5);
            },

            downloadImage() {
                const download = document.getElementById("downloadPhoto");
                const canvas = document.getElementById("photoTaken").toDataURL("image/jpeg")
                    .replace("image/jpeg", "image/octet-stream");
                download.setAttribute("href", canvas);
            }
        },
        created(){
            this.getTechniquePlaces();
        }
    }
</script>

<style scoped>
    .scan-confirmation {
        position: absolute;
        width: 100%;
        height: 250px;

        background-color: rgba(255, 255, 255, .8);

        display: flex;
        flex-flow: row nowrap;
        justify-content: center;
    }
    .is_shipping {
        width: 100%;background-color: #70aee2 !important;border-color: #70aee2 !important;
    }
    .is_moving {
        width: 100%; background-color: darkorange !important;border-color: darkorange !important;
    }
    .v-application--wrap {
        min-height: auto !important;
    }
    .v-select__selection {
        font-weight: bold !important;
    }
    .v-application--is-ltr .v-text-field .v-label {
        font-size: 24px !important;
        font-weight: normal !important;
    }
    .v-dialog__content {
        top: 30% !important;
        position: absolute !important;
    }
    .text-field {
        font-size: 20px !important;
    }
    #input-5 {
        font-size: 20px !important;
    }
    .btn{
        font-size: 20px !important;
    }
    .div-buttons {
        display: flex;
        justify-content: space-between;
    }
</style>
