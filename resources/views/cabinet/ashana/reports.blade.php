@extends('cabinet.cabinet')
@push('cabinet_styles')
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">

    @include('cabinet.inc.vue-styles')

@endpush
@section('content')
    <div class="row" id="emp_kitchen">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Асхана отчеты</h3>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <v-app>
                        <v-main>
                            <v-container>
                                <v-row>
                                    <v-col md="3">
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

                                    <v-col md="3">
                                        <v-menu
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

                                    <v-col md="3">
                                        <v-autocomplete
                                            :search-input.sync="searchInput"
                                            :hint="`${companies.id}, ${companies.short_ru_name}`"
                                            :items="companies"
                                            v-model="company_id"
                                            item-value="id"
                                            item-text="short_ru_name"
                                            label="-----------------"
                                            required
                                        ></v-autocomplete>
                                    </v-col>

                                    <v-col md="3">
                                        <v-autocomplete
                                            :hint="`${kitchens.value}, ${kitchens.text}`"
                                            :items="kitchens"
                                            v-model="kitchen_id"
                                            item-value="value"
                                            item-text="text"
                                            label="Столовая"
                                            required
                                        ></v-autocomplete>
                                    </v-col>

                                    <v-col md="3">
                                        <v-autocomplete
                                            :hint="`${kitchens_reports_type.value}, ${kitchens_reports_type.text}`"
                                            :items="kitchens_reports_type"
                                            v-model="krt_id"
                                            item-value="value"
                                            item-text="text"
                                            label="Тип отчета"
                                            required
                                        ></v-autocomplete>
                                    </v-col>

                                    <v-col md="3">
                                        <v-text-field
                                            v-if="krt_id === 1"
                                            v-model="count_work_day"
                                            type="number"
                                            label="Количество рабочих дней"
                                            required
                                        ></v-text-field>
                                    </v-col>

                                    <v-col md="3">
                                        <v-btn type="button" @click="getAshanaLogs()" class="warning">Показать</v-btn>
                                        <v-btn type="button" @click="generateAshanaLogs()" class="primary">Скачать отчет</v-btn>
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

                                <template v-if="krt_id === 0">
                                    <v-card>
                                        <v-card-title>
                                            Список пользователей
                                            <v-spacer></v-spacer>
                                            <v-text-field
                                                v-model="search"
                                                append-icon="mdi-magnify"
                                                label="Поиск"
                                                single-line
                                                hide-details
                                            ></v-text-field>
                                            <v-spacer></v-spacer>
                                            <v-btn
                                                v-if="users.length > 0"
                                                @click="toExcel"
                                            >
                                                <v-icon
                                                    middle
                                                >
                                                    mdi-download
                                                </v-icon>
                                                Скачать
                                            </v-btn>
                                        </v-card-title>
                                        <v-data-table
                                            :headers="headers"
                                            :items="users"
                                            :search="search"
                                            id="printTable"
                                            disable-pagination
                                            hide-default-footer
                                        >
                                            <template v-slot:item.id="{index}">
                                                <td>@{{index+1}}</td>
                                            </template>

                                            <template v-slot:item.cnt="{item}">
                                                <td v-if="item.cnt > 1" style="background-color: red;">@{{item.cnt}}</td>
                                                <td v-else>@{{item.cnt}}</td>
                                            </template>

                                            <template v-slot:item.din_type="{item}">
                                                <span v-if="item.din_type == 1">Стандарт</span>
                                                <span v-else>Булочки</span>
                                            </template>
                                        </v-data-table>

                                    </v-card>
                                </template>

                                <template v-if="krt_id === 1">
                                    <v-card>
                                        <v-card-title>
                                            Список пользователей
                                            <v-spacer></v-spacer>
                                            <v-text-field
                                                v-model="search"
                                                append-icon="mdi-magnify"
                                                label="Поиск"
                                                single-line
                                                hide-details
                                            ></v-text-field>
                                            <v-spacer></v-spacer>
                                            <v-btn
                                                v-if="users_new.length > 0"
                                                @click="toExcel"
                                            >
                                                <v-icon
                                                    middle
                                                >
                                                    mdi-download
                                                </v-icon>
                                                Скачать
                                            </v-btn>
                                        </v-card-title>
                                        <v-data-table
                                            :headers="headers_new"
                                            :items="users_new"
                                            :search="search"
                                            id="printTable"
                                            disable-pagination
                                            hide-default-footer
                                        >
                                            <template v-slot:item.id="{index}">
                                                <td>@{{index+1}}</td>
                                            </template>

                                            <template v-slot:item.summ="{item}">
                                                <td>@{{ parseInt(item.abk) + parseInt(item.kpp3) + parseInt(item.mob) }}</td>
                                            </template>

                                            <template v-slot:item.work_day="{item}">
                                                <td>@{{ count_work_day }}</td>
                                            </template>

                                            <template v-slot:item.one_hundred="{item}">
                                                <td>@{{ parseInt(item.abk) + parseInt(item.kpp3) + parseInt(item.mob) > count_work_day ? (parseInt(item.abk) + parseInt(item.kpp3) + parseInt(item.mob)) - count_work_day : 0 }}</td>
                                            </template>
                                        </v-data-table>

                                    </v-card>
                                </template>
                            </v-container>
                        </v-main>
                    </v-app>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@push('cabinet_scripts')

    @include('cabinet.inc.vue-scripts')

    <script>
        new Vue({
            el: '#emp_kitchen',
            vuetify: new Vuetify(),
            data () {
                return {
                    dialog: false,
                    dialog_success: false,
                    company_id: 0,
                    iin:"",
                    phone: "",
                    fullname: "",
                    success_html: "",
                    search: '',
                    searchInput: '',
                    errors: [],
                    companies: <?php echo json_encode($companies) ?>,
                    from_date: new Date().toISOString().substr(0, 10),
                    to_date: new Date().toISOString().substr(0, 10),
                    menu2: false,
                    menu3: false,
                    standart: 500,
                    bulochki: 500,
                    headers: [
                        {
                            text: '№',
                            align: 'start',
                            sortable: false,
                            value: 'id',
                        },
                        { text: 'Компания', value: 'company_name' },
                        { text: 'Ф.И.О', value: 'full_name' },
                        { text: 'Должность', value: 'p_name' },
                        { text: 'Тип обеда', value: 'din_type' },
                        { text: 'Талонов', value: 'cnt' },
                    ],
                    headers_new: [
                        {
                            text: '№',
                            align: 'start',
                            sortable: false,
                            value: 'id',
                        },
                        { text: 'Компания', value: 'company_name' },
                        { text: 'Ф.И.О', value: 'full_name' },
                        { text: 'Должность', value: 'p_name' },
                        { text: 'АБК', value: 'abk' },
                        { text: 'КПП3', value: 'kpp3' },
                        { text: 'Мобильная', value: 'mob' },
                        { text: 'Сумма', value: 'summ' },
                        { text: 'Рабочих дней', value: 'work_day' },
                        { text: 'Превышение, 100% оплата', value: 'one_hundred' },
                    ],
                    users: [],
                    users_new: [],
                    link_excel: false,
                    href_excel: "",
                    user_id: 0,
                    is_blocked: 0,
                    blocks: [
                        { text: 'Активно', value: 0 },
                        { text: 'Заблокирован', value: 1}
                    ],
                    kitchens: [
                        //{ text: 'ИП Акмуратов А.М', value: 1099 },
                        { text: 'ИП Cargotraffic(АБК + Мобильная + КПП3)', value: 1097}
                    ],
                    kitchen_id: 0,
                    kitchens_reports_type: [
                        { text: 'Старый отчет', value: 0 },
                        { text: 'Новый отчет', value: 1}
                    ],
                    krt_id: 0,
                    count_work_day: 22,
                    progress_circule: false,
                    hide: true
                }
            },
            methods: {
                getAshanaLogs() {
                    let formData = new FormData();
                    formData.append('company_id', this.company_id);
                    formData.append('cashier_id', this.kitchen_id);
                    formData.append('from_date', this.from_date);
                    formData.append('to_date', this.to_date);
                    formData.append('krt_id', this.krt_id);
                    formData.append('count_work_day', this.count_work_day);
                    axios.post('/cabinet/ashana/get-logs', formData)
                        .then(res => {
                            console.log(res.data)
                            if(this.krt_id === 0) {
                                this.users = res.data;
                            } else {
                                this.users_new = res.data;
                            }

                        })
                        .catch(err => console.log(err))
                },
                toExcel() {
                    let file_name = 'stolovaya_' + Date.now() + '.xlsx';
                    if(this.company_id !== 0) {
                        let company = this.companies.find(item => item.id === this.company_id);
                        file_name = company.short_en_name + '_' + Date.now() + '.xlsx';
                    }
                    var workbook = XLSX.utils.table_to_book(document.getElementById('printTable'));
                    XLSX.writeFile(workbook, file_name);
                },
                generateAshanaLogs(){
                    this.progress_circule = true;
                    let formData = new FormData();
                    formData.append('company_id', this.company_id);
                    formData.append('cashier_id', this.kitchen_id);
                    formData.append('from_date', this.from_date);
                    formData.append('to_date', this.to_date);
                    axios.post('/cabinet/ashana/generate-reports', formData)
                        .then(res => {
                            console.log(res.data)
                            window.location.href = res.data;
                            this.progress_circule = false;
                        })
                        .catch(err => {
                            this.progress_circule = false;
                            console.log(err);
                        })
                }
            }
        })
    </script>
@endpush
