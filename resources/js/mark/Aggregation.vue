<template>
    <div :class="(overlay) ? 'reload' : ''">
        <div class="text-center">
            <v-overlay :value="overlay">
                <v-progress-circular
                    :width="4"
                    :size="50"
                    color="white"
                    indeterminate
                ></v-progress-circular>
            </v-overlay>
        </div>
        <v-stepper style="width: 1106px; height: 396px; text-align: left; margin:0;" v-model="Step">
            <v-stepper-header>
                <v-stepper-step :complete="Step > 1" step="1">
                    SSCC паллеты
                </v-stepper-step>
                <v-divider></v-divider>
                <v-stepper-step :complete="Step > 2" step="2">
                    SSCC короба
                </v-stepper-step>
                <v-divider></v-divider>
                <v-stepper-step step="3">
                    КМ штук
                </v-stepper-step>
            </v-stepper-header>
            <v-stepper-items>
                <v-stepper-content step="1">
                    <v-card class="mb-12" color="white lighten-1" height="150px">
                        <div class="textTitle" style="width: 400px; display: flex; flex-wrap: wrap; gap: 5px;">
                            <h2>Отсканируйте SSCC паллеты</h2>
                        </div>
                        <input v-model="inputValueSSCC" style="width: 800px; height: 60px; border: 1px solid black; border-radius: 10px; padding-left: 10px; font-size: 18px; margin-top: 10px;" placeholder="Данное поле не должно оставаться пустым" type="text">
                    </v-card>
                    <div class="btn" style="width: 1000px; height: 40px; display: flex; margin-top: 5px; align-items: center; justify-content: center;">
                        <v-btn :disabled="validStepOne" style="background: green; color: white;" @click="handleClickStepOne">
                            Продолжить
                        </v-btn>
                    </div>
                </v-stepper-content>
                <v-stepper-content step="2">
                    <v-card class="mb-12" color="white" height="200px">
                        <div class="textTitle" style="width: 800px; margin: 0;">
                            <h3 style="width: 800px;">Отсканируйте SSCC короба</h3>
                            <h3 style="width: 800px;">Отсканировано коробов: {{this.scanProductBoxToPal == 0 ? 0 : this.scanProductBoxToPal}} / {{this.standartBoxToPal}}</h3>
                            <h3 style="width: 800px;">Последний отсканированный короб: {{this.lastBox == null ? 'Нет отсканированных коробов' : this.lastBox}}</h3>
                        </div>
                        <input v-model="inputValueBox" style="width: 800px; height: 60px; border: 1px solid black; border-radius: 10px; padding-left: 10px; font-size: 18px; margin-top: 10px;" placeholder="Данное поле не должно оставаться пустым" type="text">
                    </v-card>
                    <div class="btn" style="width: 1000px; height: 40px; display: flex; margin-top: 5px; align-items: center; justify-content: center; gap: 20px;">
                        <v-btn :disabled="validStepTwo" style="background: green; color: white;" @click="handleClickStepTwo">
                            Продолжить
                        </v-btn>
                        <v-btn style="background: green; color: white;" @click="Step = 1">
                            Назад
                        </v-btn>
                    </div>
                </v-stepper-content>
                <v-stepper-content step="3">
                    <v-card class="mb-12" color="white lighten-1" height="200px">
                        <div class="textTitle" style="width: 800px; margin: 0;">
                            <h2 style="width: 800px;">Отсканируйте SSCC штуки</h2>
                            <h3 style="width: 800px;">Отсканировано штук: {{this.scanProductItemToBox == 0 ? 0 : this.scanProductItemToBox}} / {{this.standartItemToBox}} </h3>
                            <h3 style="width: 800px;">Последняя отсканированная штука: {{this.lastItem == null ? 'Нет отсканированных штук' : this.lastItem}} </h3>
                        </div>
                        <input @keyup.enter="hendaleEnter" v-model="inputValueItem" style="width: 800px; height: 60px; border: 1px solid black; border-radius: 10px; padding-left: 10px; font-size: 18px; margin-top: 10px;" placeholder="Данное поле не должно оставаться пустым" type="text">
                    </v-card>
                    <div class="btn" style="width: 1000px; height: 40px; display: flex; margin-top: 5px; align-items: center; justify-content: center; gap: 20px;">
                        <v-btn :disabled="complete" style="background: green; color: white;" @click="handleFinish">
                            Завершить
                        </v-btn>
                        <v-btn style="background: green; color: white;" @click="Step = 2">
                            Назад
                        </v-btn>
                    </div>
                </v-stepper-content>
            </v-stepper-items>
        </v-stepper>

        <div class="text-center">
            <v-dialog v-model="dialog" width="500">
                <v-card>
                    <v-card-title class="text-h5 green lighten-2">
                        ❗❗ Предупреждение ❗❗
                    </v-card-title>
                    <v-card-text class="text-h4" style="color: rgb(255, 1, 1);">
                        Стандарт штук в коробе выполнен
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="green" text @click="handleFinish">
                            ОК
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </div>
        <div class="text-center">
            <v-dialog v-model="dialogBox" width="500">
                <v-card>
                    <v-card-title class="text-h5 green lighten-2">
                        ❗❗ Предупреждение ❗❗
                    </v-card-title>
                    <v-card-text class="text-h4" style="color: rgb(255, 1, 1);">
                        Стандарт коробов на паллете выполнен
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="green" text @click="handelModalBox">
                            ОК
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </div>
        <div class="text-center">
            <v-dialog v-model="dialogError" width="500">
                <v-card>
                    <v-card-title class="text-h5 green lighten-2">
                        ❗❗ Предупреждение ❗❗
                    </v-card-title>
                    <v-card-text class="text-h4" style="color: rgb(255, 1, 1);">
                        {{ dialogErrorText }}
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="green" text @click="handelErrorModal">
                            ОК
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </div>
    </div>

</template>
<script>
    import axios from 'axios';
    export default  {
        data() {
            return {
                Step: 1,
                inputValueSSCC: '',
                inputValueBox: '',
                inputValueItem: '',
                dialog: false,
                dialogBox: false,
                overlay: false,
                complete: true,
                standartItemToBox: '',
                scanProductItemToBox: 0,
                standartBoxToPal: '',
                scanProductBoxToPal: 0,
                lastBox: null,
                lastItem: null,
                dialogError: false,
                dialogErrorText: ''
            }
        },
        computed: {
            validStepOne: function () {
                return this.inputValueSSCC.trim().length == 0 ? true : false
            },
            validStepTwo: function () {
                return this.inputValueBox.trim().length == 0 ? true : false
            }
        },
        methods: {
            handleClickStepOne() {
                let SSCCPalData = new FormData()
                SSCCPalData.append('SSCCPal', this.inputValueSSCC)
                this.overlay = true
                axios.post('/marking-manager/aggregation/pallet', SSCCPalData)
                .then(res => {
                    console.log(res)
                    this.overlay = false
                    this.standartBoxToPal = res.data.standard
                    this.scanProductBoxToPal = res.data.fact
                    this.lastBox = res.data.lastBox
                    this.Step = 2;
                })
                .catch(err => {
                    console.log(err);
                    this.overlay = false
                    this.dialogErrorText = "Ошибка!"
                    this.dialogError = true
                });
                // ЧЕрез GEt высылаю номер SSCC палеты в ответ буду получать все стандарты и сколько уже отсканирован
            },

            handleClickStepTwo() {
                let SsccPalAndBox = new FormData()
                SsccPalAndBox.append('SSCCPal', this.inputValueSSCC)
                SsccPalAndBox.append('SSCCBox', this.inputValueBox)
                this.overlay = true
                // Через Get высылаю номер короба в ответ получаю стандарт штук в коробке а также коробов и сколько отсканировано и сколько отсканировано
                axios.post('/marking-manager/aggregation/pallet/box', SsccPalAndBox)
                .then(res => {
                    this.overlay = false
                    this.scanProductItemToBox = res.data.fact
                    this.standartItemToBox = res.data.standardBox
                    this.lastItem = res.data.lastProduct
                    if (this.standartItemToBox == this.scanProductItemToBox && this.scanProductBoxToPal == this.standartBoxToPal) {
                        this.dialogBox = true
                    } else {
                        this.inputValueItem = ''
                        this.Step = 3
                    }
                })
                .catch(err => {
                    console.log(err)
                    this.overlay = false
                    this.dialogErrorText = "Ошибка!"
                    this.dialogError = true
                })

            },
            hendaleEnter () {
                let SsccPalBoxItem = new FormData()
                SsccPalBoxItem.append('SSCCPal', this.inputValueSSCC)
                SsccPalBoxItem.append('SSCCBox', this.inputValueBox)
                SsccPalBoxItem.append('km', this.inputValueItem)
                this.overlay = true
                axios.post('/marking-manager/aggregation/pallet/box/product', SsccPalBoxItem)
                .then(res => {
                    this.scanProductItemToBox = res.data.fact
                    this.lastItem = res.data.lastProduct
                    this.inputValueItem = ''
                    this.overlay = false
                    this.validationStepThree()
                })
                .catch(err => {
                    if (err.response.statusCode === 400) {
                        this.dialogErrorText = "Ошибка данная штука уже отсканирована!"
                        this.overlay = false
                        this.dialogError = true
                        this.inputValueItem = ''
                    } else {
                        this.dialogErrorText = "Ошибка!"
                        this.overlay = false
                        this.dialogError = true
                        this.inputValueItem = ''
                    }

                })
            },
            handleFinish() {
                let SsccPalBoxItem = new FormData()
                SsccPalBoxItem.append('SSCCPal', this.inputValueSSCC)
                SsccPalBoxItem.append('SSCCBox', this.inputValueBox)
                this.overlay = true
                axios.post('marking-manager/aggregation/pallet/box/stats', SsccPalBoxItem)
                .then(res => {
                    this.overlay = false
                    this.standartBoxToPal = res.data.standardPallet
                    this.scanProductBoxToPal = res.data.factBox
                    this.lastBox = res.data.lastBox
                    if ( this.standartBoxToPal == this.scanProductBoxToPal) {
                        this.dialog = false
                        this.inputValueBox = ''
                        this.Step = 2;
                        this.dialogBox = true
                    } else {
                        this.dialog = false
                        this.inputValueBox = ''
                        this.Step = 2;
                    }
                })
                .catch(err => {
                    console.log(err)
                    this.overlay = false
                    this.dialogErrorText = "Ошибка!"
                    this.dialogError = true
                })

            },
            validationStepThree() {
                // Через GET высылаю номер SSCC короба , а в ответ получаю стандар и сколько уже отсканировано
                if (this.standartItemToBox == this.scanProductItemToBox) {
                    this.complete = false
                    this.dialog = true
                }
            },
            handelModalBox() {
                this.dialogBox = false
                this.inputValueSSCC = ''
                this.Step = 1
            },
            handelErrorModal() {
                this.dialogError = false
            }
        },
    }
</script>
<style scoped>

    .v-stepper-items {
        position: relative;
    }
    .v-application .text-center {
        position: absolute;
        top: 50%;
        z-index: 1000;
        left: 50%;
    }
    .reload {
        filter: brightness(40%)
    }
</style>
