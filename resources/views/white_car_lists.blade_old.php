@extends('layouts.wcl')
@push('styles')
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <style>
        .dataTables_filter input {
            margin-left: 0 !important;
            display: block !important;
            width: 100% !important;
        }
    </style>
@endpush
@section('content')
    <div id="wcl3_kkk" class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="card-title">Список постоянных машин</h3>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <v-app>
                        <v-main>
                            <v-container>
                                <template>
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>Гос.номер</th>
                                            <th>Клиент</th>
                                            <th>КПП</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($lists as $list)
                                            <tr>
                                                <td>
                                                    <v-btn type="button" @click="fixDateInTime({{ $list->wcl_com_id }})" class="primary">{{ $list->gov_number }}</v-btn>
                                                </td>
                                                <td>{{ $list->short_ru_name }}</td>
                                                <td>{{ strtoupper($list->kpp_name) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </template>

                                <template>
                                    <v-dialog style="z-index: 99999; position: relative;" v-model="dialog" persistent max-width="400px">
                                        <v-card>
                                            <v-card-title>

                                            </v-card-title>
                                            <v-card-text>
                                                <v-container>
                                                    <v-row>
                                                        <v-col cols="12" class="text-center">
                                                            <v-icon style="font-size: 60px; color: green; margin-bottom: 20px;">
                                                                mdi-check-circle
                                                            </v-icon>
                                                            <p v-html="successTxt" style="font-size: 20px; color: green;">

                                                            </p>
                                                        </v-col>
                                                    </v-row>
                                                </v-container>
                                            </v-card-text>
                                            <v-card-actions>
                                                <v-spacer></v-spacer>
                                                <v-btn color="blue darken-1" @click="dialog=false">Закрыть</v-btn>
                                            </v-card-actions>
                                        </v-card>
                                    </v-dialog>
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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-print@0.3.0/dist/vueprint.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <!-- DataTables -->
    <script src="/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>



    <script>
        $(function () {
            $("#example1").DataTable({
                "responsive": true,
                "autoWidth": false,
                "pageLength": 10,
                "bLengthChange": false,
                "bFilter": true,
                "language": {
                    "url": "/dist/Russian.json"
                },
            });
        });
    </script>

    <script>
        const wcl_kkk = new Vue({
            el: '#wcl3_kkk',
            vuetify: new Vuetify(),
            data () {
                return {
                    dialog: false,
                    successTxt: ""
                }
            },
            methods: {
                fixDateInTime(id) {
                    let formData = new FormData();
                    formData.append('wcl_com_id', id);
                    axios.post(`/white-car-lists/${id}/fix-date-in-time`, formData)
                        .then(res => {
                            console.log(res)
                            this.successTxt = `Номер машины: ${res.data.gov_number}. Заезд зафиксирован!`
                            this.dialog = true;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                }
            }
        })
    </script>
@endpush
