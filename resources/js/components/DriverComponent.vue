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
                                        <label>В какую компанию?</label>
                                        <input v-model="company" tabindex="9" type="text" name="company" required class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label>Вид операции</label>
                                        <select tabindex="10" v-model="operation_type" id="type" class="form-control">
                                            <option value="1">Погрузка</option>
                                            <option value="2">Разгрузка</option>
                                            <option value="3">Другие действие</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Грузоподъемность ТС</label>
                                        <v-select
                                            class="form-control"
                                            :hint="`${categories_tc.id}, ${categories_tc.title}`"
                                            :items="categories_tc"
                                            v-model="cat_tc_id"
                                            item-value="id"
                                            item-text="title"
                                        ></v-select>
                                    </div>

                                    <div class="form-group">
                                        <label>Тип кузова</label>
                                        <v-select
                                            class="form-control"
                                            :hint="`${body_types.id}, ${body_types.title}`"
                                            :items="body_types"
                                            v-model="body_type_id"
                                            item-value="id"
                                            item-text="title"
                                        ></v-select>
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
                                        <button @click="orderPermitByDriver()" style="padding: 10px 50px; font-size: 20px;" type="button" class="btn btn-success">Заказать пропуск</button>
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
                company: '',
                operation_type: 1,
                ud_number: '',
                is_driver: false,
                wantToOrder: false,
                dialog_success: false,
                success_html: '',
                cat_tc_id: 0,
                body_type_id: 0,
                png: '',
                errors: [],
            }
        },
        props: [
            'categories_tc',
            'body_types'
        ],
        methods: {
            isDriver(){
                let formData = new FormData();
                formData.append('tex_number', this.tex_number);
                formData.append('ud_number', this.ud_number);
                axios.post('/check-driver', formData)
                    .then(res => {
                        console.log(res, res.data.cat_tc_id);
                        this.is_driver = true;
                        this.dialog_success = false;
                        this.cat_tc_id = res.data.cat_tc_id;
                        this.body_type_id = res.data.body_type_id;
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
                if (!this.company) {
                    this.errors.push('Укажите компанию');
                }
                if (this.cat_tc_id == 0) {
                    this.errors.push('Укажите грузоподъемность ТС');
                }
                if (this.body_type_id == 0) {
                    this.errors.push('Укажите тип кузова');
                }

                if (this.errors.length == 0) {
                    formData.append('tex_number', this.tex_number);
                    formData.append('ud_number', this.ud_number);
                    formData.append('company', this.company);
                    formData.append('operation_type', this.operation_type);
                    formData.append('cat_tc_id', this.cat_tc_id);
                    formData.append('body_type_id', this.body_type_id);
                    formData.append('wantToOrder', this.wantToOrder);
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
                            this.company = '';
                            this.operation_type = 1;
                            this.cat_tc_id = 0;
                            this.body_type_id = 0;
                            this.is_driver = false;
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
