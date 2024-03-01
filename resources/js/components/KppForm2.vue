<template>
    <div class="row">
        <div class="col-md-12 left_content">
            <div class="row">
                <div class="col-md-6">
                    <h4 style="font-style: italic;">Данные о машине</h4>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>№тех.паспорта</label>
                                <input onkeyup="return no_cirilic(this);" placeholder="AB020111" style="text-transform: uppercase;" v-model="tex_number" tabindex="1" type="text" name="tex_number" required class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4" style="padding-top: 52px;">
                            <button @click="checkCar()" type="button" class="btn btn-warning">Проверить</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Гос.номер</label>
                                <input onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="gov_number" tabindex="2" type="text" placeholder="888BBZ05" required name="gov_number" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Номер прицепа</label>
                                <input onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="pr_number" tabindex="4" type="text" name="pr_number" class="form-control">
                            </div>
                        </div>
                    </div>


                    <div class="form-group">
                        <label>Марка авто</label>
                        <input v-model="mark_car" type="text" tabindex="3" name="mark_car" class="form-control">
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Грузоподъемность ТС</label>
                                <!--                        <input v-model="company" tabindex="9" type="text" name="company" required class="form-control">-->
                                <v-select
                                    :items="capacity"
                                    class="form-control"
                                    :hint="`${capacity.id}, ${capacity.title}`"
                                    item-value="id"
                                    v-model="capacity_id"
                                    item-text="title"
                                ></v-select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Тип кузова</label>
                                <v-select
                                    :items="bodytypes"
                                    class="form-control"
                                    :hint="`${bodytypes.id}, ${bodytypes.title}`"
                                    item-value="id"
                                    v-model="bt_id"
                                    item-text="title"
                                ></v-select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Дата заезда</label>
                        <datetime input-class="form-control" type="datetime" v-model="date_in" format="yyyy-MM-dd HH:mm" value-zone="Asia/Almaty" :phrases="{ok: 'ОК', cancel: 'Отмена'}"></datetime>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4 style="font-style: italic;">Данные о водителе</h4>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>№вод.удостоверение</label>
                                <input placeholder="BN203526" onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="ud_number" tabindex="6" type="text" name="ud_number" required class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4" style="padding-top: 52px;">
                            <button @click="checkDriver()" style="color: #fff;" type="button" class="btn btn-dark">Проверить</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>ФИО</label>
                                <input style="text-transform: uppercase;" v-model="last_name" tabindex="7" type="text" name="last_name" required class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Телефон(без пробелов с 8-ки)</label>
                        <input v-model="phone" tabindex="8" type="text" placeholder="87778882255" name="phone" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Компания</label>
                        <v-autocomplete
                            :items="companies"
                            class="form-control"
                            :hint="`${companies.id}, ${companies.short_en_name}`"
                            :search-input.sync="searchInput"
                            item-value="id"
                            v-model="company_id"
                            item-text="short_en_name"
                        ></v-autocomplete>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label style="font-size: 14px;">Копия документов*</label>
                        <div class="row justify-content-center">
                            <div class="col-md-12 text-center">
                                <div id="camera" style="max-width: 100%; margin-bottom: 10px;"></div>
                                <div>
                                    <button style="margin-right: 20px;" id="image-fire" type=button class="btn btn-warning">Фото (лицевая)</button>
                                    <button style="color: #fff;" id="tex_btn" type=button class="btn btn-dark">Фото (обратная)</button>
                                </div>
                            </div>
                            <input type="hidden" id="path_docs_fac" name="path_docs_fac" class="image-tag">
                            <input type="hidden" id="path_docs_back" name="path_docs_back" class="image-tag">
                        </div>
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
                    <div class="form-group">
                        <button @click="createPermitByKpp()" style="padding: 10px 50px; font-size: 20px;" type="button" class="btn btn-success">Создать пропуск и печатать</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 right_content">
            <div>
                <h3>Список добавленных пропусков</h3>
            </div>
            <hr>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">ФИО</th>
                    <th scope="col">#Тех.паспорт</th>
                    <th scope="col">#Вод.удос.</th>
                    <th scope="col">Компания</th>
                    <th scope="col">Телефон</th>
                    <th scope="col">Гос.номер</th>
                    <th scope="col">Действие</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(permit, i) in permits" :key="i">
                    <th scope="row">{{ permit.id }}</th>
                    <td>{{ permit.last_name }}</td>
                    <td>{{ permit.tex_number }}</td>
                    <td>{{ permit.ud_number }}</td>
                    <td>{{ permit.company }}</td>
                    <td>{{ permit.phone }}</td>
                    <td>{{ permit.gov_number }}</td>
                    <td>
                        <v-icon
                            title="Распечатать пропуск"
                            middle
                            @click="print_r(permit.id)"
                        >
                            mdi-printer
                        </v-icon>
                    </td>
                </tr>
                </tbody>
            </table>
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
                gov_number: '',
                tex_number: '',
                mark_car: '',
                pr_number: '',
                date_in: this.currentDateTime(),
                ud_number: '',
                last_name: '',
                phone: '',
                company: '',
                errors: [],
                permits: [],
                searchInput: '',
                company_id: 0,
                capacity_id: 0,
                bt_id: 0,
            }
        },
        props: [
            'datetime',
            'companies',
            'capacity',
            'bodytypes',
        ],
        methods: {
            checkCar(){
                axios.get('/get-car-info/'+this.tex_number)
                    .then(res => {
                        this.gov_number = res.data.gov_number;
                        this.mark_car = res.data.mark_car;
                        this.pr_number = res.data.pr_number;
                        this.capacity_id = res.data.lc_id;
                        this.bt_id = res.data.bt_id;
                    })
                    .catch(err => {
                        if(err.response.status === 401) {
                            window.location.href = '/login';
                        } else {
                            console.log(err)
                        }
                    })
            },
            checkDriver(){
                axios.get('/get-driver-info/'+this.ud_number)
                    .then(res => {
                        this.last_name = res.data.fio;
                        this.phone = res.data.phone;
                    })
                    .catch(err => {
                        if(err.response.status === 401) {
                            window.location.href = '/login';
                        } else {
                            console.log(err)
                        }
                    })
            },
            createPermitByKpp(){
                this.errors = [];
                if (!this.gov_number) {
                    this.errors.push('Укажите номер машины');
                }
                if (!this.tex_number) {
                    this.errors.push('Укажите номер тех.паспорта');
                }
                if (!this.mark_car) {
                    this.errors.push('Укажите марку');
                }
                if (!this.date_in) {
                    this.errors.push('Укажите дату заезда');
                }
                if (!this.ud_number) {
                    this.errors.push('Укажите номер водительского удостоверение');
                }
                if (!this.last_name) {
                    this.errors.push('Укажите ФИО');
                }
                if (!this.phone) {
                    this.errors.push('Укажите телефон');
                }
                if (this.company_id === 0) {
                    this.errors.push('Укажите компанию');
                }
                if (this.capacity_id === 0) {
                    this.errors.push('Укажите грузоподъемность ТС');
                }
                if (this.bt_id === 0) {
                    this.errors.push('Укажите тип кузова');
                }

                if (this.errors.length === 0) {
                    const config = {
                        headers: { 'content-type': 'multipart/form-data' }
                    };

                    let formData = new FormData();
                    formData.append('gov_number', this.gov_number);
                    formData.append('tex_number', this.tex_number);
                    formData.append('mark_car', this.mark_car);
                    formData.append('pr_number', this.pr_number);
                    formData.append('date_in', this.date_in);
                    formData.append('ud_number', this.ud_number);
                    formData.append('last_name', this.last_name);
                    formData.append('phone', this.phone);
                    formData.append('company_id', this.company_id);
                    formData.append('lc_id', this.capacity_id);
                    formData.append('bt_id', this.bt_id);

                    formData.append('path_docs_fac', $('#path_docs_fac').val());
                    formData.append('path_docs_back', $('#path_docs_back').val());

                    axios.post('/order-permit-by-kpp', formData, config)
                        .then(res => {
                            this.gov_number = '';
                            this.tex_number = '';
                            this.mark_car = '';
                            this.pr_number = '';
                            this.date_in = this.currentDateTime();
                            this.ud_number = '';
                            this.last_name = '';
                            this.phone = '';
                            this.company_id = 0;
                            this.capacity_id = 0;
                            this.bt_id = 0;
                            $('#path_docs_fac').val('');
                            $('#path_docs_back').val('');
                            this.permits = this.getPermits();
                            $("body,html").animate({
                                scrollTop: 0
                            }, 800);
                        })
                        .catch(err => {
                            if(err.response.status === 401) {
                                window.location.href = '/login';
                            }
                            if (err.response.status === 403) {
                                this.errors.push(err.response.data);
                            }
                        })
                }
            },
            getPermits(){
                axios.get('/get-permits-list')
                    .then(res => {
                        this.permits = res.data;
                    })
                    .catch(err => {
                        if(err.response.status === 401) {
                            window.location.href = '/login';
                        } else {
                            console.log(err)
                        }
                    })
            },
            print_r(id){
                axios.get('/command/print/'+id)
                    .then(res => {
                        console.log(res)
                    })
                    .catch(err => {
                        if(err.response.status === 401) {
                            window.location.href = '/login';
                        } else {
                            console.log(err)
                        }
                    })
            },
            currentDateTime(){
                let d = new Date();
                return d.getFullYear()+ "-" + (((d.getMonth()+1) < 10)?"0":"") + (d.getMonth()+1) + "-" + ((d.getDate() < 10)?"0":"") + d.getDate() +"T"+ ((d.getHours() < 10)?"0":"") + d.getHours() + ":"+ ((d.getMinutes() < 10)?"0":"") + d.getMinutes();
            },
        },
        created(){
            this.getPermits();
        }
    }
</script>
