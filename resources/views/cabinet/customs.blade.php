@extends('cabinet.cabinet')
@push('cabinet_styles')

    @include('cabinet.inc.vue-styles')

    <link rel="stylesheet" href="/css/report_menu.css">
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div id="adApp" class="card-body">
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



                                            <v-col md="3">
                                                <v-btn type="button" @click="getPermits()" class="primary">Показать</v-btn>
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
                                    </v-container>
                                </template>

                                <template>
                                    <v-card>
                                        <v-card-title>
                                            Список пропусков
                                            <v-spacer></v-spacer>
                                            <v-text-field
                                                v-model="search"
                                                append-icon="mdi-magnify"
                                                label="Поиск"
                                                single-line
                                                hide-details
                                            ></v-text-field>
                                            <v-spacer></v-spacer>
                                            <v-btn v-if="download_btn" style="background: #28A745; color: #fff;" class="btn btn-success" @click="toExcel">
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
                                            :items="permits"
                                            :search="search"
                                            :loading="isLoaded"
                                            loading-text="Загружается... Пожалуйста подождите"
                                            id="printTable"
                                            disable-pagination
                                            hide-default-footer
                                        >
                                            <template v-slot:item.operation_type="{item}">
                                                <span v-if="item.operation_type == 1">Погрузка</span>
                                                <span v-else-if="item.operation_type == 2">Разгрузка</span>
                                                <span v-else>Другие действие</span>
                                            </template>

                                            <template v-slot:item.lc_id="{item}">
                                                <span v-if="item.lc_id == 1">Легковые</span>
                                                <span v-else-if="item.lc_id == 2">до 10 тонн</span>
                                                <span v-else>Грузовые</span>
                                            </template>

                                            {{--<template v-slot:item.bt_id="{item}">
                                                <span v-if="item.bt_id == 1">Рефрижератор (холод)</span>
                                                <span v-else>Обычный</span>
                                            </template>

                                            <template v-slot:item.is_driver="{item}">
                                                <span v-if="item.is_driver == 1">Водитель</span>
                                                <span v-else-if="item.is_driver == 2">Клиент</span>
                                                <span v-else>КПП</span>
                                            </template>--}}
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
            el: '#adApp',
            vuetify: new Vuetify(),
            data(){
                return {
                    company_id: <?php echo Auth::user()->company_id ?>,
                    companies: <?php echo json_encode($companies) ?>,
                    search: '',
                    download_btn: false,
                    searchInput: '',
                    errors: [],
                    permits: [],
                    from_date: new Date().toISOString().substr(0, 10),
                    to_date: new Date().toISOString().substr(0, 10),
                    headers: [
                        { text: 'КПП', value: 'kpp_name' },
                        { text: 'Телефон водителя', value: 'phone' },
                        { text: 'Номер ТС', value: 'gov_number' },
                        { text: 'Номер прицепа', value: 'pr_number' },
                        { text: 'Тип операции', value: 'operation_type' },
                        { text: 'Груз.под', value: 'lc_id' },
                        { text: 'Номер инвойса/CMR', value: 'invoice_cmr_number' },
                        { text: 'Пропуск оформлен', value: 'date_in' },
                    ],
                    menu2: false,
                    menu3: false,
                    progress_circule: false,
                    isLoaded: false,
                }
            },
            methods: {
                getPermits(){
                    this.isLoaded = true;
                    this.permits = [];
                    let formData = new FormData();
                    formData.append('from_date', this.from_date);
                    formData.append('to_date', this.to_date);
                    formData.append('company_id', this.company_id);

                    axios.post('/cabinet/get/permits-customs', formData)
                        .then(res => {
                            this.permits = res.data;
                            this.isLoaded = false;
                            this.download_btn = true;
                        })
                        .catch(err => {
                            this.isLoaded = false;
                            console.log(err)
                        })
                },
                downloadReport(){
                    this.progress_circule = true;
                    let formData = new FormData();
                    formData.append('from_date', this.from_date);
                    formData.append('to_date', this.to_date);
                    formData.append('company_id', this.company_id);
                    axios.post('/cabinet/reports/download-report', formData)
                        .then(res => {
                            console.log(res.data);
                            window.location.href = res.data;
                            this.progress_circule = false;
                        })
                        .catch(err => {
                            console.log(err);
                        })

                },
                toExcel() {
                    var workbook = XLSX.utils.table_to_book(document.getElementById('printTable'));
                    XLSX.writeFile(workbook, 'otchet.xlsx');
                }
            }
        })
    </script>
@endpush
