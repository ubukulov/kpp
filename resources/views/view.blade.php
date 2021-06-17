<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/driver.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <style>
        input, button{
            font-size: 14px !important;
        }
        label{
            font-size: 14px !important;
        }
    </style>
    <title>Детальная информация по пропускам</title>
</head>
<body>
<div class="container-fluid" id="app">
    <v-app>
        <v-main>
            <v-container>
                <div class="row">
                    {{--<div class="col-md-4">
                        <p><strong>Кол-во пропусков за текущий месяц: </strong>{{ $cur_month[0]->cnt }}</p>
                        <p><strong>Кол-во пропусков за предыдущий месяц: </strong>{{ $pre_month[0]->cnt }}</p>
                    </div>--}}

                    <div class="col-md-12">
                        <v-row>
                            <v-col md="2">
                                <v-menu
                                    v-model="menu2"
                                    :close-on-content-click="false"
                                    :nudge-right="40"
                                    transition="scale-transition"
                                    offset-y
                                    min-width="290px"
                                >
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-text-field
                                            v-model="from_date"
                                            label="C"
                                            prepend-icon="event"
                                            readonly
                                            v-bind="attrs"
                                            v-on="on"
                                        ></v-text-field>
                                    </template>
                                    <v-date-picker v-model="from_date" :first-day-of-week="1" locale="ru" @input="menu2 = false"></v-date-picker>
                                </v-menu>
                            </v-col>

                            <v-col md="2">
                                <v-menu
                                    class="menu3"
                                    v-model="menu3"
                                    :close-on-content-click="false"
                                    :nudge-right="40"
                                    transition="scale-transition"
                                    offset-y
                                    min-width="290px"
                                >
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-text-field
                                            v-model="to_date"
                                            label="ПО"
                                            prepend-icon="event"
                                            readonly
                                            v-bind="attrs"
                                            v-on="on"
                                        ></v-text-field>
                                    </template>
                                    <v-date-picker v-model="to_date" :first-day-of-week="1" locale="ru" @input="menu3 = false"></v-date-picker>
                                </v-menu>
                            </v-col>

                            <v-col md="2">
                                <div class="form-group">
                                    <v-autocomplete
                                        :items="companies"
                                        :hint="`${companies.id}, ${companies.short_en_name}`"
                                        :search-input.sync="searchInput"
                                        item-value="id"
                                        v-model="company_id"
                                        item-text="short_en_name"
                                        autocomplete
                                    ></v-autocomplete>
                                </div>
                            </v-col>

                            <v-col md="2">
                                <div class="form-group">
                                    <v-autocomplete
                                        :items="reports"
                                        :hint="`${reports.id}, ${reports.text}`"
                                        item-value="id"
                                        v-model="report_id"
                                        item-text="text"
                                        autocomplete
                                    ></v-autocomplete>
                                </div>
                            </v-col>

                            <v-col md="2">
                                <div class="form-group">
                                    <v-autocomplete
                                        :items="kpps"
                                        :hint="`${kpps.id}, ${kpps.text}`"
                                        item-value="id"
                                        v-model="kpp_id"
                                        item-text="text"
                                        autocomplete
                                    ></v-autocomplete>
                                </div>
                            </v-col>

                            <v-col md="2">
                                <v-btn @click="downloadReport()" type="button" class="success">Скачать</v-btn>
                            </v-col>

                            <v-col md="12">
                                <div v-if="progress_circule" class="text-center">
                                    <v-progress-circular
                                        style="width: 100px; height: 100px;"
                                        indeterminate
                                        color="primary"
                                    ></v-progress-circular>
                                </div>
                            </v-col>
                        </v-row>
                    </div>

                    <div class="col-md-12 left_content">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 style="font-style: italic;">Данные о машине</h4>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>№тех.паспорта</label>
                                            <input placeholder="AB020111" style="text-transform: uppercase;" v-model="tex_number" tabindex="1" type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="padding-top: 40px;">
                                        <button @click="checkCar()" type="button" class="btn btn-warning">Проверить</button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Гос.номер</label>
                                            <input disabled style="text-transform: uppercase;" v-model="gov_number" tabindex="2" type="text" placeholder="888BBZ05" required name="gov_number" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Номер прицепа</label>
                                            <input v-model="pr_number" tabindex="4" disabled type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>Марка авто</label>
                                    <input v-model="mark_car" type="text" tabindex="3" disabled class="form-control">
                                </div>

                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Грузоподъемность ТС</label>
                                            <input v-model="lc_name" tabindex="9" disabled type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Тип кузова</label>
                                            <input v-model="bt_name" tabindex="9" disabled type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Собственник по техпаспорту/частник</label>
                                    <input type="text" class="form-control" disabled v-model="from_company">
                                </div>

                                <div class="form-group">
                                    <label>Какая транспортная компания наняла?</label>
                                    <input type="text" class="form-control" disabled v-model="employer_name">
                                </div>

                                <div class="form-group">
                                    <label>Дата заезда</label>
                                    <input tabindex="5" v-model="date_in" disabled type="text" id="date_in" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h4 style="font-style: italic;">Данные о водителе</h4>

                                <div class="form-group">
                                    <label>№вод.удостоверение</label>
                                    <input placeholder="BN203526" disabled style="text-transform: uppercase;" v-model="ud_number" tabindex="6" type="text" name="ud_number" required class="form-control">
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>ФИО</label>
                                            <input style="text-transform: uppercase;" v-model="last_name" tabindex="7" type="text" disabled class="form-control">
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>Телефон(без пробелов с 8-ки)</label>
                                    <input v-model="phone" tabindex="8" type="text" placeholder="87778882255" disabled class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Компания</label>
                                    <input v-model="company" tabindex="8" type="text" placeholder="87778882255" disabled class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Вид операции</label>
                                    <input v-model="operation_name" tabindex="8" type="text" disabled class="form-control">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Маршрут</label>
                                            <input v-model="to_city" tabindex="8" type="text" disabled class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Иностранная машина?</label>
                                            <input v-model="foreign_car" tabindex="8" type="text" disabled class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Дата выезда</label>
                                    <input v-model="date_out" tabindex="8" type="text" disabled class="form-control">
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label style="font-size: 14px;">Документ (лицевая)</label>
                                    <img :src="imgF" style="max-width: 100%;" alt="">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label style="font-size: 14px;">Документ (Обратная)</label>
                                    <img :src="imgB" style="max-width: 100%;" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </v-container>
        </v-main>
    </v-app>
</div>
<script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
<script>
    new Vue({
        el: '#app',
        vuetify: new Vuetify(),
        data () {
            return {
                mark_car: '',
                gov_number: '',
                tex_number: '',
                pr_number: '',
                operation_name: '',
                last_name: '',
                from_company: '',
                searchInput: '',
                to_city: '',
                phone: '',
                ud_number: '',
                lc_name: '',
                bt_name: '',
                employer_name: '',
                date_in: '',
                date_out: '',
                foreign_car: '',
                company: '',
                imgF: '',
                imgB: '',
                from_date: '',
                to_date: '',
                menu2: false,
                menu3: false,
                progress_circule: false,
                company_id: 0,
                companies: <?php echo json_encode($companies) ?>,
                reports: [
                    {id: 0, text: 'Лог'},
                    {id: 1, text: 'Свод'},
                ],
                report_id: 0,
                kpps: [
                    {id: 0, text: 'Все'},
                    {id: 1, text: 'КПП 2'},
                    {id: 2, text: 'КПП 4'},
                    {id: 3, text: 'КПП 5'},
                ],
                kpp_id: 0,
            }
        },
        methods: {
            checkCar(){
                axios.get('/view-detail-info-permit/get-car-info/'+this.tex_number)
                    .then(res => {
                        console.log(res)
                        this.gov_number = res.data.gov_number
                        this.mark_car = res.data.mark_car
                        this.pr_number = res.data.pr_number
                        this.date_in = res.data.date_in
                        this.date_out = res.data.date_out
                        this.company = res.data.company
                        this.last_name = res.data.last_name
                        this.ud_number = res.data.ud_number
                        if(res.data.lc_id == 1) {
                            this.lc_name = 'Легковая';
                        } else if(res.data.lc_id == 2) {
                            this.lc_name = 'до 10тонн';
                        } else {
                            this.lc_name = 'Грузовая';
                        }

                        if(res.data.operation_type == 1) {
                            this.operation_name = 'Погрузка';
                        } else if(res.data.operation_type == 2) {
                            this.operation_name = 'Разгрузка';
                        } else {
                            this.operation_name = 'Другие действия';
                        }

                        if(res.data.foreign_car == 0) {
                            this.foreign_car = 'Не указано';
                        } else if(res.data.foreign_car == 1) {
                            this.foreign_car = 'Казахстанская';
                        } else {
                            this.foreign_car = 'Иностранная';
                        }

                        if(res.data.bt_id == 1) {
                            this.bt_name = 'Рефрижератор (холод)';
                        } else {
                            this.bt_name = 'Обычный';
                        }

                        this.from_company = res.data.from_company
                        this.employer_name = res.data.employer_name
                        this.to_city = res.data.to_city
                        this.imgF = res.data.path_docs_fac
                        this.imgB = res.data.path_docs_back
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            checkDriver(){
                axios.get('/view-detail-info-permit/get-driver-info/'+this.ud_number)
                    .then(res => {
                        this.last_name = res.data.fio
                        this.phone = res.data.phone
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            downloadReport(){
                this.progress_circule = true;
                let formData = new FormData();
                formData.append('from_date', this.from_date);
                formData.append('to_date', this.to_date);
                formData.append('company_id', this.company_id);
                formData.append('report_id', this.report_id);
                formData.append('kpp_id', this.kpp_id);
                axios.post('/view-detail-info-permit/download-permits-for-selected-time', formData)
                    .then(res => {
                        window.location.href = res.data;
                        this.progress_circule = false;
                    })
                    .catch(err => {
                        console.log(err)
                    })
            }
        }
    });
</script>
</body>
</html>
