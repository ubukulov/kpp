<template>
    <div class="row">
        <div class="col-md-12 left_content">
            <div><h3>Разрешение на въезд</h3></div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h4 style="font-style: italic;">Данные о машине</h4>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Гос.номер(без пробелов, на анг)</label>
                                <input onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="gov_number" tabindex="1" type="text" placeholder="888BBZ05" required name="gov_number" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4" style="padding-top: 52px;">
                            <button @click="checkCar()" type="button" class="btn btn-warning">Проверить</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>№тех.паспорта</label>
                        <input style="text-transform: uppercase;" v-model="tex_number" tabindex="2" type="text" name="tex_number" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Марка авто</label>
                        <input v-model="mark_car" type="text" tabindex="3" name="mark_car" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Номер прицепа</label>
                        <input v-model="pr_number" tabindex="4" type="text" name="pr_number" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Дата заезда</label>
                        <input tabindex="5" v-model="date_in" type="text" id="date_in" required name="date_in" class="form-control">
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
                    <div class="form-group">
                        <label>ФИО</label>
                        <input style="text-transform: uppercase;" v-model="last_name" tabindex="7" type="text" name="last_name" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Телефон(без пробелов с 8-ки)</label>
                        <input v-model="phone" tabindex="8" type="text" placeholder="87778882255" name="phone" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Компания</label>
<!--                        <input v-model="company" tabindex="9" type="text" name="company" required class="form-control">-->
                        <v-autocomplete
                            :items="companies"
                            :class="form-control"
                            :hint="`${companies.id}, ${companies.short_en_name}`"
                            :search-input.sync="searchInput"
                            item-value="id"
                            v-model="company_id"
                            item-text="short_en_name"
                            autocomplete
                        ></v-autocomplete>
                    </div>

                    <div class="form-group">
                        <label>Вид операции</label>
                        <select v-model="operation_type" tabindex="10" name="operation_type" id="type" class="form-control">
                            <option value="1">Погрузка</option>
                            <option value="2">Разгрузка</option>
                            <option value="3">Другие действие</option>
                        </select>
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
                        <label>Компьютер</label>
                        <select v-model="computer_name" tabindex="10" name="computer_name" class="form-control">
                            <option value="1">КПП №2 (бутик №1)</option>
                            <option value="2">КПП №2 (бутик №2)</option>
                        </select>
                    </div>

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
                    <th scope="col">#вод.удос.</th>
                    <th scope="col">Компания</th>
                    <th scope="col">Вид операции</th>
                    <th scope="col">Телефон</th>
                    <th scope="col">Гос.номер</th>
                    <th scope="col">Печать</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(permit, i) in permits" :key="i">
                    <th scope="row">{{ permit.id }}</th>
                    <td>{{ permit.last_name }}</td>
                    <td>{{ permit.ud_number }}</td>
                    <td>{{ permit.company }}</td>
                    <td v-if="permit.operation_type == 1">
                        Погрузка
                    </td>
                    <td v-else-if="permit.operation_type == 2">
                        Разгрузка
                    </td>
                    <td v-else>
                        Другие действие
                    </td>
                    <td>{{ permit.phone }}</td>
                    <td>{{ permit.gov_number }}</td>
                    <td>
                        <v-icon
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
    export default {
        data(){
            return {
                gov_number: '',
                tex_number: '',
                mark_car: '',
                pr_number: '',
                date_in: '',
                ud_number: '',
                last_name: '',
                phone: '',
                company: '',
                operation_type: 1,
                errors: [],
                permits: [],
                searchInput: '',
                company_id: 0,
                computer_name: 1
            }
        },
        props: [
            'datetime',
            'companies'
        ],
        methods: {
            checkCar(){
                axios.get('/get-car-info/'+this.gov_number)
                    .then(res => {
                        console.log(res)
                        this.tex_number = res.data.tex_number
                        this.mark_car = res.data.mark_car
                        this.pr_number = res.data.pr_number
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            checkDriver(){
                axios.get('/get-driver-info/'+this.ud_number)
                    .then(res => {
                        console.log(res.data.fio)
                        this.last_name = res.data.fio
                        this.phone = res.data.phone
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            searchPermit(){
                this.html = ''
                let formData = new FormData();
                formData.append('search', this.search)
                axios.post('/search/permit', formData)
                    .then(res => {
                        console.log(res)
                        // this.html = '<tr style="background: cadetblue;">'
                        this.html = this.html + '<th scope="row">'+res.data.id+'</th>'
                        this.html = this.html + '<td>'+res.data.last_name+'</td>'
                        this.html = this.html + '<td>'+res.data.ud_number+'</td>'
                        this.html = this.html + '<td>'+res.data.company+'</td>'
                        if(res.data.operation_type == 1) {
                            this.html = this.html + '<td>Погрузка</td>'
                        } else if(res.data.operation_type == 2) {
                            this.html = this.html + '<td>Разгрузка</td>'
                        } else {
                            this.html = this.html + '<td>Другие действие</td>'
                        }
                        this.html = this.html + '<td>'+res.data.phone+'</td>'
                        this.html = this.html + '<td>'+res.data.gov_number+'</td>'
                        this.html = this.html + '<td><v-icon middle v-on:click="print_r('+res.data.id+')">mdi-printer</v-icon></td>'
                        // $("tbody").prepend(this.html)
                    })
                    .catch(err => {
                        console.log(err)
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
                if (this.company_id == 0) {
                    this.errors.push('Укажите компанию');
                }

                if (this.errors.length == 0) {
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
                    formData.append('operation_type', this.operation_type);
                    formData.append('computer_name', this.computer_name);
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
                        this.operation_type = 1;
                        $('#path_docs_fac').val('');
                        $('#path_docs_back').val('');
                        this.permits = this.getPermits();
                    })
                    .catch(err => {
                        console.log(err)
                    })
                }
            },
            getPermits(){
                axios.get('/get-permits-list')
                    .then(res => {
                        this.permits = res.data;
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            print_r(id){
                axios.get('/command/print/'+id+'/'+this.computer_name)
                .then(res => {
                    console.log(res)
                })
                .catch(err => {
                    console.log(err)
                })
            },
            currentDateTime(){
                let d = new Date();
                return ((d.getDate() < 10)?"0":"") + d.getDate() +"."+(((d.getMonth()+1) < 10)?"0":"") + (d.getMonth()+1) +"."+ d.getFullYear()+" "+((d.getHours() < 10)?"0":"") + d.getHours() +":"+ ((d.getMinutes() < 10)?"0":"") + d.getMinutes();
            }
        },
        created(){
            this.date_in = this.datetime;
            this.getPermits();
            console.log(this.currentDateTime())
        }
    }
</script>

<style scoped>

</style>
