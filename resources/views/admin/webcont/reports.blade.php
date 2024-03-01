@extends('admin.admin')
@push('admin_styles')
    <!-- DataTables -->
    {{--    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">--}}
    {{--    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">--}}

    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/css/report_menu.css">
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12">
                            Отчеты по крановым операциям
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div id="adApp" class="card-body">
                    <v-app>
                        <v-main>
                            <v-container>
                                <template>
                                    <v-container>
                                        <v-row>
                                            <v-col md="4">
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

                                            <v-col md="4">
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

                                            <v-col md="4">
                                                <v-btn type="button" @click="getStats()" class="primary">Скачать отчет</v-btn>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </template>

                                {{--<template>
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
                                            <v-btn @click="toExcel">
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
                                            id="printTable"
                                            disable-pagination
                                            hide-default-footer
                                        >
                                            <template v-slot:item.id="{item, index}">
                                                <span>@{{ index+1 }}</span>
                                            </template>

                                            <template v-slot:item.full_name="{item}">
                                                <span>@{{ item.full_name }}</span>
                                            </template>

                                            <template v-slot:item.spreder="{item}">
                                                <span>@{{ item['techs'][1].cnt }}</span>
                                            </template>

                                            <template v-slot:item.avn="{item}">
                                                <span>@{{ item['techs'][2].cnt }}</span>
                                            </template>

                                            <template v-slot:item.av561="{item}">
                                                <span>@{{ item['techs'][3].cnt }}</span>
                                            </template>

                                            <template v-slot:item.rich="{item}">
                                                <span>@{{ item['techs'][4].cnt }}</span>
                                            </template>

                                            <template v-slot:item.cr30="{item}">
                                                <span>@{{ item['techs'][5].cnt }}</span>
                                            </template>

                                            <template v-slot:item.cr50="{item}">
                                                <span>@{{ item['techs'][6].cnt }}</span>
                                            </template>

                                            <template v-slot:item.av952="{item}">
                                                <span>@{{ item['techs'][7].cnt }}</span>
                                            </template>

                                            <template v-slot:item.av296="{item}">
                                                <span>@{{ item['techs'][8].cnt }}</span>
                                            </template>
                                        </v-data-table>

                                    </v-card>
                                </template>--}}
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

@push('admin_scripts')
    <!-- DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-print@0.3.0/dist/vueprint.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <script>
        new Vue({
            el: '#adApp',
            vuetify: new Vuetify(),
            data(){
                return {
                    company_id: 0,
                    search: '',
                    searchInput: '',
                    errors: [],
                    permits: [],
                    from_date: new Date().toISOString().substr(0, 10),
                    to_date: new Date().toISOString().substr(0, 10),
                    headers: [
                        {
                            text: '№',
                            align: 'start',
                            sortable: false,
                            value: 'id',
                        },
                        { text: 'Ф.И.О', value: 'full_name' },
                        { text: 'Спредер', value: 'spreder' },
                        { text: 'Автокран-наёмный', value: 'avn' },
                        { text: 'Автокран-561', value: 'av561' },
                        { text: 'Ричстакер', value: 'rich' },
                        { text: 'Кран-30', value: 'cr30' },
                        { text: 'Кран-50', value: 'cr50' },
                        { text: 'Автокран-952', value: 'av952' },
                        { text: 'Автокран-296', value: 'av296' },
                    ],
                }
            },
            methods: {
                getStats(){
                    let formData = new FormData();
                    formData.append('from_date', this.from_date);
                    formData.append('to_date', this.to_date);

                    axios.post('/admin/webcont/get-reports', formData)
                        .then(res => {
                            // this.permits = res.data;
                            // console.log(res.data[0]['techs'][1].cnt);
                            window.location.href = res.data;
                        })
                        .catch(err => {
                            console.log(err)
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
