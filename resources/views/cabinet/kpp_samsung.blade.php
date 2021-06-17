@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- DataTables -->
    {{--    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">--}}
    {{--    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">--}}

    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <style>
        #printTable {
            max-height: 520px;
            overflow: auto;
        }
    </style>
@endpush
@section('content')
    <input type="hidden" id="report_name" value="{{ "Report_".date("H")."_".date("i")."_".date('s') }}">
    <div id="kpp_sam">
        <v-app>
            <v-main>
                <v-container>
                    <v-row>
                        <v-col cols="12">
                            <!-- general form elements -->
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Настройка</h3>
                                </div>
                                <!-- /.card-header -->
                                <!-- form start -->
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Код документа</label>
                                        <input type="text" v-on:keyup.enter="get100Logs()" class="form-control" v-model="document" value="" placeholder="Максимум 20 знаков">
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <button @click="findByDocumentCode()" type="button" class="btn btn-success">Найти</button>
                                </div>
                            </div>

                            <div class="card card-primary">
                                <template>
                                    <v-card>
                                        <v-card-title>
                                            Список
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
                                            :items="logs"
                                            :search="search"
                                            id="printTable"
                                            disable-pagination
                                            hide-default-footer
                                        >

                                        </v-data-table>

                                    </v-card>
                                </template>

                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </v-main>
        </v-app>
    </div>
@stop

@push('cabinet_scripts')
    <!-- DataTables -->
    {{--<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>--}}

    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-print@0.3.0/dist/vueprint.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <script>
        new Vue({
            el: '#kpp_sam',
            vuetify: new Vuetify(),
            data(){
                return {
                    search: '',
                    searchInput: '',
                    document: '',
                    download_btn: false,
                    headers: [
                        { text: '#', value: 'index' },
                        { text: 'Дата', value: 'VALUE-1' },
                        { text: 'Код документа', value: 'VALUE-2' },
                    ],
                    logs: []
                }
            },
            methods: {
                toExcel() {
                    var workbook = XLSX.utils.table_to_book(document.getElementById('printTable'));
                    var file_name = $('#report_name').val() + '.xlsx';
                    XLSX.writeFile(workbook, file_name);
                },
                get100Logs(){
                    this.document = '';
                    axios.get('/cabinet/kpp/samsung/get100logs')
                        .then(res => {
                            this.logs = res.data;
                            this.download_btn = true;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                findByDocumentCode(){
                    let formData = new FormData();
                    formData.append('document', this.document);

                    axios.post('/cabinet/kpp/samsung/get-document-by-code', formData)
                        .then(res => {
                            this.logs = [];
                            this.logs = res.data;
                            this.document = '';
                            this.download_btn = true;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                }
            },
            created(){
                this.get100Logs();
            }
        })
    </script>
@endpush
