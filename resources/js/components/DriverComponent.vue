<template>
    <v-app>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 left_content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="driver_content">
                                <div>
                                    <h3>Заказ предварительного пропуска</h3>
                                </div>
                                <hr>

                                <div class="form-group">
                                    <label>№Тех.паспорта</label>
                                    <input onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="tex_number" tabindex="1" type="text" placeholder="AP00024215" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>№Вод.удостоверения</label>
                                    <input placeholder="BN203526" onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="ud_number" tabindex="6" type="text" class="form-control">
                                </div>

                                <div class="form-group">
                                    <button @click="isDriver()" type="button" class="btn btn-warning">Проверить</button>
                                </div>

                                <hr>

                                <div v-if="is_driver" id="driver_pod">

                                    <div class="form-group">
                                        <label>Вид операции</label>
                                        <select tabindex="10" v-model="operation_type" id="type" class="form-control">
                                            <option value="1">Погрузка</option>
                                            <option value="2">Разгрузка</option>
                                            <option value="3">Другие действия</option>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Грузоподъемность ТС</label>
                                                <v-select
                                                    class="form-control"
                                                    :hint="`${categories_tc.id}, ${categories_tc.title}`"
                                                    :items="categories_tc"
                                                    v-model="lc_id"
                                                    item-value="id"
                                                    item-text="title"
                                                ></v-select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Тип кузова</label>
                                                <v-select
                                                    class="form-control"
                                                    :hint="`${body_types.id}, ${body_types.title}`"
                                                    :items="body_types"
                                                    v-model="bt_id"
                                                    item-value="id"
                                                    item-text="title"
                                                ></v-select>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="operation_type != 3" class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Маршрут</label>
                                                <v-autocomplete
                                                    :items="directions"
                                                    class="form-control"
                                                    :hint="`${directions.id}, ${directions.title}`"
                                                    item-value="id"
                                                    v-model="direction_id"
                                                    item-text="title"
                                                    autocomplete
                                                ></v-autocomplete>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div v-if="direction_id == 6" class="form-group">
                                                <label>Напишите</label>
                                                <input style="font-size: 16px !important;" type="text" class="form-control" v-model="to_city">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-check">
                                        <input type="checkbox" v-model="wantToOrder" class="form-check-input" id="exampleCheck1">
                                        <label class="form-check-label" for="exampleCheck1">Я хочу получать выгодные заказы!</label>
                                    </div>

                                    <div class="form-group">
                                        <p v-if="errors.length" style="margin-bottom: 0px !important;">
                                            <b>Пожалуйста исправьте указанные ошибки:</b>
                                        <ul style="color: red; padding-left: 15px; list-style: circle; text-align: left;">
                                            <li v-for="error in errors">{{error}}</li>
                                        </ul>
                                        </p>
                                    </div>

                                    <div class="form-group">
                                        <button :disabled="orderButtonDisabled" @click="orderPermitByDriver()" style="padding: 10px 50px; font-size: 20px;" type="button" class="btn btn-success">Заказать пропуск</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <v-dialog style="z-index: 99999; position: relative;" v-model="dialog_success" persistent max-width="400px">
                    <v-card>
                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12">
                                        <div style="text-align: center;">
                                            <div v-html="png">

                                            </div>

                                            <div style="margin-top: 30px;" v-html="success_html">

                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="blue" style="color: #fff;" @click="dialog_success = false">Закрыть</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </div>
        </div>
    </v-app>
</template>

<script>
    import axios from 'axios'
    export default {
        data () {
            return {
                tex_number: '',
                operation_type: 1,
                ud_number: '',
                is_driver: false,
                wantToOrder: false,
                dialog_success: false,
                success_html: '',
                lc_id: 0,
                bt_id: 0,
                png: '',
                errors: [],
                to_city: '',
                direction_id: 0,
                orderButtonDisabled: false
            }
        },
        props: [
            'categories_tc',
            'body_types',
            'directions'
        ],
        methods: {
            isDriver(){
                let formData = new FormData();
                formData.append('tex_number', this.tex_number);
                formData.append('ud_number', this.ud_number);
                axios.post('/check-driver', formData)
                    .then(res => {
                        this.is_driver = true;
                        this.dialog_success = false;
                        this.lc_id = res.data.lc_id;
                        this.bt_id = res.data.bt_id;
                    })
                    .catch(err => {
                        console.log(err);
                        this.png = '<img src="https://www.nicepng.com/png/full/67-677160_wrong-clip-art-clip-art.png" style="max-width: 100%; width: 100px;" />';
                        this.success_html = '<p style="font-size: 30px; line-height: 30px;">Проверка не пройдена, Вам необходимо пройти первичную регистрацию на КПП.</p>';
                        this.is_driver = false;
                        this.dialog_success = true;
                    })
            },
            orderPermitByDriver(){
                let formData = new FormData();
                this.errors = [];
                if (!this.tex_number) {
                    this.errors.push('Укажите номер тех.паспорта');
                }
                if (!this.ud_number) {
                    this.errors.push('Укажите номер водительского удостоверение');
                }
                if (this.lc_id == 0) {
                    this.errors.push('Укажите грузоподъемность ТС');
                }
                if (this.bt_id == 0) {
                    this.errors.push('Укажите тип кузова');
                }

                if (this.operation_type != 3) {
                    if (this.direction_id == 0) {
                        this.errors.push('Укажите маршрут');
                    }
                    if (this.direction_id == 6) {
                        if (!this.to_city) {
                            this.errors.push('Укажите названия маршрута');
                        }
                    }
                }

                if (this.errors.length == 0) {
                    this.orderButtonDisabled = true;
                    formData.append('tex_number', this.tex_number);
                    formData.append('ud_number', this.ud_number);
                    formData.append('operation_type', this.operation_type);
                    formData.append('lc_id', this.lc_id);
                    formData.append('bt_id', this.bt_id);
                    formData.append('direction_id', this.direction_id);
                    formData.append('wantToOrder', this.wantToOrder);

                    if(this.operation_type != 3) {
                        formData.append('to_city', this.to_city);
                    }

                    axios.post('/order-permit-by-driver', formData)
                        .then(res => {
                            console.log(res);
                            this.png = '<img src="https://www.nicepng.com/png/detail/362-3624869_icon-success-circle-green-tick-png.png" style="max-width: 100%; width: 100px;" />';
                            this.success_html = '<p style="font-size: 30px; line-height: 30px;">Пропуск №'+res.data.id+' успешно оформлен!</p>';
                            if(this.wantToOrder) {
                                this.success_html = this.success_html + "<p style='color: #000;font-size:18px;'>Для получения информации о заказах, Вы можете написать на WhatsApp <span style='color: green !important;'>(<a style='color: green !important;' href='https://api.whatsapp.com/send?phone=77777022000' target='_blank'>8 777 702 2000</a>)</span>, либо дождаться звонка от нашего менеджера.</p>";
                            }
                            this.dialog_success = true;
                            this.tex_number = '';
                            this.ud_number = '';
                            this.operation_type = 1;
                            this.lc_id = 0;
                            this.bt_id = 0;
                            this.is_driver = false;
                            this.to_city = '';
                            this.direction_id = 0;
                            this.orderButtonDisabled = false;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                }
            }
        }
    }
</script>

<style scoped>

</style>
