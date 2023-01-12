@extends('admin.admin')
@push('admin_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
@endpush
@section('content')
    <div class="row" id="container_logs">
        <v-app>
            <v-main>
                <v-container>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <v-row>
                                    <v-col cols="2">
                                        <v-text-field
                                            v-model="number"
                                            solo
                                            label="Номер контейнера"
                                            clearable
                                        ></v-text-field>
                                    </v-col>

                                    <v-col cols="2">
                                        <v-menu
                                            ref="menu1"
                                            v-model="menu1"
                                            :close-on-content-click="false"
                                            :return-value.sync="date1"
                                            transition="scale-transition"
                                            offset-y
                                            min-width="auto"
                                        >
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-text-field
                                                    v-model="date1"
                                                    label="С"
                                                    prepend-icon="mdi-calendar"
                                                    readonly
                                                    v-bind="attrs"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-date-picker
                                                v-model="date1"
                                                no-title
                                                scrollable
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn
                                                    text
                                                    color="primary"
                                                    @click="menu1 = false"
                                                >
                                                    Cancel
                                                </v-btn>
                                                <v-btn
                                                    text
                                                    color="primary"
                                                    @click="fixDate1()"
                                                >
                                                    OK
                                                </v-btn>
                                            </v-date-picker>
                                        </v-menu>
                                    </v-col>

                                    <v-col cols="2">
                                        <v-menu
                                            ref="menu2"
                                            v-model="menu2"
                                            :close-on-content-click="false"
                                            :return-value.sync="date2"
                                            transition="scale-transition"
                                            offset-y
                                            min-width="auto"
                                        >
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-text-field
                                                    v-model="date2"
                                                    label="По"
                                                    prepend-icon="mdi-calendar"
                                                    readonly
                                                    v-bind="attrs"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-date-picker
                                                v-model="date2"
                                                no-title
                                                scrollable
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn
                                                    text
                                                    color="primary"
                                                    @click="menu2 = false"
                                                >
                                                    Cancel
                                                </v-btn>
                                                <v-btn
                                                    text
                                                    color="primary"
                                                    @click="fixDate2()"
                                                >
                                                    OK
                                                </v-btn>
                                            </v-date-picker>
                                        </v-menu>
                                    </v-col>

                                    <v-col cols="2">
                                        <v-btn
                                            class="btn btn-success"
                                            style="background: #28a745 !important; background-color: #28a745 !important;"
                                            @click="searchContainer()"
                                        >Найти</v-btn>
                                    </v-col>

                                    <v-col cols="2">
                                        <v-btn
                                            v-if="container_logs.length > 0"
                                            class="btn btn-dark"
                                            style="background: #28a745 !important; background-color: #28a745 !important;"
                                            @click="download()"
                                        >Скачать</v-btn>
                                    </v-col>
                                </v-row>

                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <v-data-table
                                    :headers="headers"
                                    :items="container_logs"
                                    :items-per-page="20"
                                    :search="search"
                                    id="printTable"
                                    :loading="isLoaded"
                                    loading-text="Загружается... Пожалуйста подождите"
                                >
                                    <template v-slot:item.operation_type="{ item }">
                                        <div v-if="item.operation_type === 'incoming'">
                                            На приход
                                        </div>
                                        <div v-else-if="item.operation_type === 'received'">
                                            Размещен/перемещен
                                        </div>
                                        <div v-else-if="item.operation_type === 'in_order'">
                                            На выдачу
                                        </div>
                                        <div v-else-if="item.operation_type === 'shipped'">
                                            Отобран
                                        </div>
                                        <div v-else>
                                            Выдан
                                        </div>
                                    </template>

                                    <template v-slot:item.created_at="{ item }">
                                        @{{ new Date(item.created_at).toLocaleString() }}
                                    </template>
                                </v-data-table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                </v-container>
            </v-main>
        </v-app>
    </div>
@stop

@push('admin_scripts')
    <!-- DataTables -->
    <script src="/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <script>
        $(function () {
            $("#example1").DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "/dist/Russian.json"
                },
            });
        });
    </script>

    <script>
        new Vue({
            el: '#container_logs',
            vuetify: new Vuetify(),
            data(){
                return {
                    headers: [
                        {
                            text: 'ID',
                            align: 'start',
                            sortable: false,
                            value: 'id',
                        },
                        {
                            text: 'Номер контейнера',
                            align: 'start',
                            sortable: true,
                            value: 'container_number',
                        },
                        { text: 'Пользователь', value: 'full_name'},
                        { text: 'Операции', value: 'operation_type'},
                        { text: 'Тип техники', value: 'technique' },
                        { text: 'ИЗ', value: 'address_from' },
                        { text: 'В', value: 'address_to' },
                        { text: 'Состояние', value: 'state' },
                        { text: 'Компания', value: 'company' },
                        { text: 'Дата', value: 'created_at' },
                    ],
                    container_logs: [],
                    isLoaded: true,
                    search: '',
                    number: '',
                    date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                    date1: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                    menu1: false,
                    date2: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                    menu2: false,
                }
            },
            methods: {
                getContainerLogs(){
                    this.container_logs = [];
                    this.isLoaded = true;

                    axios.get('/admin/webcont/get/logs')
                        .then(res => {
                            console.log(res);
                            this.container_logs = res.data.data;
                            this.isLoaded = false;
                        })
                        .catch(err => {
                            console.log(err);
                        })
                },
                searchContainer(){
                    this.container_logs = [];
                    this.isLoaded = true;
                    let formData = new FormData();
                    formData.append('number', this.number);
                    formData.append('date1', this.date1);
                    formData.append('date2', this.date2);
                    axios.post('/admin/webcont/search', formData)
                        .then(res => {
                            console.log(res.data)
                            this.container_logs = res.data;
                            this.isLoaded = false;
                        })
                        .catch(err => {
                            console.log(err);
                        })
                },
                fixDate1(){
                    this.$refs.menu1.save(this.date1);
                },
                fixDate2(){
                    this.$refs.menu2.save(this.date2);
                },
                download(){
                    let file_name = 'webcont_' + Date.now() + '.xlsx';
                    var workbook = XLSX.utils.table_to_book(document.getElementById('printTable'));
                    XLSX.writeFile(workbook, file_name);
                }
            },
            created(){
                this.getContainerLogs();
                console.log('refs: ',this.$refs)
            }
        });
    </script>
@endpush
