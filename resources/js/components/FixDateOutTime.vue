<template>
    <div class="row" style="background: #5c966b; height: 100vh;">
        <div class="col-md-12">
            <div style="width: 800px; max-width: 100%; margin: 50px auto; text-align: center;">
                <div class="col-md-12">
                    <div class="form-group">
                        <v-checkbox
                            v-model="setDateOutByHand"
                            label="Указать дату выезда вручную"
                        ></v-checkbox>
                        <datetime input-class="form-control" :disabled="setDateOutByHand == false" type="datetime" v-model="date_out" format="dd.MM.yyyy HH:mm" value-zone="Asia/Almaty" :phrases="{ok: 'ОК', cancel: 'Отмена'}" placeholder="Укажите дату"></datetime>
                    </div>

                    <div class="form-group">
                        <input ref="permit" autofocus v-on:keyup.enter="fixDateOut()" type="text" v-model="permit_id" class="form-control" placeholder="Введите номер пропуска">
                    </div>

                    <div class="form-group">
                        <input style="text-transform: uppercase;" onkeyup="return no_cirilic(this);" min="11" max="11" maxlength="11" type="text" v-model="outgoing_container_number" class="form-control" placeholder="Введите номер контейнера (ABCD1234567)">
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

                <div class="col-md-12">
                    <button @click="fixDateOut()" type="button" class="btn btn-info">Зафиксировать дату выезда</button>
                </div>

                <div class="col-md-12">
                    <div v-if="success" class="alert alert-success" role="alert" style="font-size: 40px;">
                        Дата выезда зафиксирована успешно!
                    </div>

                    <div v-if="failure" class="alert alert-warning" role="alert" style="font-size: 40px;">
                        {{ failure_text }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import { Datetime } from 'vue-datetime';
    import 'vue-datetime/dist/vue-datetime.css'
    export default {
        components: {
            datetime: Datetime
        },
        data(){
            return {
                permit_id: null,
                date_out: '',
                success: false,
                failure: false,
                failure_text: '',
                setDateOutByHand: false,
                interval: 0,
                outgoing_container_number: '',
                errors: []
            }
        },
        watch: {
            setDateOutByHand:function(val){
                if (val !== true) {
                    this.date_out = ''
                }
            }
        },
        methods: {
            fixDateOut(){
                this.errors = [];
                if (this.outgoing_container_number && this.outgoing_container_number.length < 11) {
                    this.errors.push('Укажите номер контейнера правильно. Например (ABCD1234567)');
                }
                if (!this.permit_id) {
                    this.errors.push('Укажите номер пропуска');
                }
                let formData = new FormData();
                if (this.permit_id != null && this.permit_id.length != 0) {
                    formData.append('permit_id', this.permit_id);
                    formData.append('outgoing_container_number', this.outgoing_container_number);
                    if (this.setDateOutByHand) {
                        formData.append('set_date_out_manual', this.setDateOutByHand);
                        formData.append('date_out', this.date_out);
                    }
                    if (this.errors.length == 0) {
                        axios.post('/fix-date-out-for-current-permit', formData)
                        .then(res => {
                            console.log(res);
                            this.success = true;
                            this.permit_id = null;
                            this.outgoing_container_number = '';
                            this.failure = false;
                            this.setDateOutByHand = false;
                            this.date_out = '';
                        })
                        .catch(err => {
                            if(err.response.status == 500) {
                                this.failure_text = 'Внимание!!! Дата выезда уже была ранее зафиксирована!';
                            } else if(err.response.status == 401) {
                                window.location.href = '/login';
                            } else {
                                this.failure_text = 'Внимание!!! Дата выезда не может быть меньше даты въезда. Проверьте еще раз!';
                            }
                            this.failure = true;
                            this.success = false;
                            this.permit_id = null;
                        })
                    }
                }
            },
            closeInfo(){
                this.success = false;
                this.failure = false;
            }
        },
        created(){
            this.interval = setInterval(() => this.closeInfo(), 5000);
        }
    }
</script>

<style scoped>
    .form-control:focus{
        background: white;
        color: #424242;
    }
</style>
