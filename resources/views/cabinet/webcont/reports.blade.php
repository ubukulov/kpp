@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- DataTables -->
    {{--    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">--}}
    {{--    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">--}}

    @include('cabinet.inc.vue-styles')

    <link rel="stylesheet" href="/css/report_menu.css">

    <style>
        .v-application--wrap {
            min-height: auto !important;
        }
        .v-main__wrap {
            width: 100vw;
        }
    </style>
@endpush
@section('content')
    <div id="adApp"  class="row">
        <v-app>
            <v-main>
                <v-container>
                    <div class="row">
                        <div class="col-md-12">
                            @if(\Illuminate\Support\Facades\Auth::user()->company->type_company == 'damu_group')
                            <div class="card" style="margin-top: 10px;">
                                <div class="card-header">
                                    Отчеты по крановым операциям
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
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
                                                :hint="`${reports.value}, ${reports.text}`"
                                                :items="reports"
                                                v-model="report_id"
                                                item-value="value"
                                                item-text="text"
                                                label="Тип отчета"
                                                required
                                            ></v-autocomplete>
                                        </v-col>

                                        <v-col md="3">
                                            <v-btn type="button" @click="getStats()" class="primary">Скачать отчет</v-btn>
                                        </v-col>
                                    </v-row>
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->

                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12">
                                            Отчет: Детализация (Крановщика)
                                        </div>
                                    </div>

                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <v-app>
                                        <v-main>
                                            <v-container>
                                                <template>
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
                                                                            label="Дата"
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
                                                                <v-autocomplete
                                                                    :hint="`${users.id}, ${users.full_name}`"
                                                                    :items="users"
                                                                    v-model="user_id"
                                                                    item-value="id"
                                                                    item-text="full_name"
                                                                    label="Выберите крановщика"
                                                                    required
                                                                ></v-autocomplete>
                                                            </v-col>

                                                            <v-col md="3">
                                                                <v-btn type="button" @click="getDetail()" class="primary">Скачать отчет</v-btn>
                                                            </v-col>
                                                        </v-row>
                                                    </v-container>
                                                </template>

                                            </v-container>
                                        </v-main>
                                    </v-app>
                                </div>
                                <!-- /.card-body -->
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12">
                                            Отчет: Ежедневной для подписи
                                        </div>
                                    </div>

                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <v-app>
                                        <v-main>
                                            <v-container>
                                                <template>
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
                                                                            label="Дата"
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
                                                                <v-autocomplete
                                                                    :hint="`${reports.value}, ${reports.text}`"
                                                                    :items="reports"
                                                                    v-model="report_id"
                                                                    item-value="value"
                                                                    item-text="text"
                                                                    label="Тип отчета"
                                                                    required
                                                                ></v-autocomplete>
                                                            </v-col>

                                                            <v-col md="3">
                                                                <v-btn type="button" @click="getStatsToday()" class="primary">Скачать отчет</v-btn>
                                                            </v-col>
                                                        </v-row>
                                                    </v-container>
                                                </template>

                                            </v-container>
                                        </v-main>
                                    </v-app>
                                </div>
                                <!-- /.card-body -->
                            </div>
                            @endif

                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12">
                                            Отчет: По колесных техники
                                        </div>
                                    </div>

                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <v-app>
                                        <v-main>
                                            <v-container>
                                                <template>
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
                                                                <v-autocomplete
                                                                    :hint="`${reports2.value}, ${reports2.text}`"
                                                                    :items="reports2"
                                                                    v-model="report_car_id"
                                                                    item-value="value"
                                                                    item-text="text"
                                                                    label="Тип отчета"
                                                                    required
                                                                ></v-autocomplete>
                                                            </v-col>

                                                            <v-col md="3">
                                                                <v-btn type="button" @click="getReportsForCar()" class="primary">Скачать отчет</v-btn>
                                                            </v-col>
                                                        </v-row>
                                                    </v-container>
                                                </template>

                                            </v-container>
                                        </v-main>
                                    </v-app>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                    </div>
                </v-container>
            </v-main>
        </v-app>
    </div>
@stop

@push('cabinet_scripts')
    <!-- DataTables -->

    @include('cabinet.inc.vue-scripts')

    <script>
        new Vue({
            el: '#adApp',
            vuetify: new Vuetify(),
            data(){
                return {
                    from_date: new Date().toISOString().substr(0, 10),
                    to_date: new Date().toISOString().substr(0, 10),
                    report_id: 0,
                    reports: [
                        { text: 'Крановщики', value: 0 },
                        { text: 'Стропальщики', value: 1}
                    ],
                    users: <?php echo json_encode($users) ?>,
                    user_id: 0,
                    reports2: [
                        { text: 'Отчет по остаткам', value: 0 },
                        { text: 'Отчет завоз и вывоз', value: 1}
                    ],
                    report_car_id: 0
                }
            },
            methods: {
                getStats(){
                    let formData = new FormData();
                    formData.append('from_date', this.from_date);
                    formData.append('to_date', this.to_date);
                    formData.append('report_id', this.report_id);

                    axios.post('/cabinet/webcont/get-reports', formData)
                        .then(res => {
                            window.location.href = res.data;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                getDetail(){
                    let formData = new FormData();
                    formData.append('from_date', this.from_date);
                    formData.append('user_id', this.user_id);

                    axios.post('/cabinet/webcont/get-detail', formData)
                        .then(res => {
                            window.location.href = res.data;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                toExcel() {
                    var workbook = XLSX.utils.table_to_book(document.getElementById('printTable'));
                    XLSX.writeFile(workbook, 'otchet.xlsx');
                },
                getStatsToday(){
                    let formData = new FormData();
                    formData.append('from_date', this.from_date);
                    formData.append('report_id', this.report_id);

                    axios.post('/cabinet/webcont/get-stats-today', formData)
                        .then(res => {
                            window.location.href = res.data;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                getReportsForCar(){
                    let formData = new FormData();
                    formData.append('from_date', this.from_date);
                    formData.append('report_id', this.report_car_id);

                    axios.post('/cabinet/webcont/get-reports-car', formData)
                        .then(res => {
                            window.location.href = res.data;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                }
            }
        })
    </script>
@endpush
