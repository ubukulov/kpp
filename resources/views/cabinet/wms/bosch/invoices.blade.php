@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- DataTables -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <style>
        .dataTables_filter input {
            margin-left: 0 !important;
            display: block !important;
            width: 100% !important;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">BOSCH. Invoices</h3>
                        </div>

                        <div class="col-6 text-right">

                        </div>

                        <div class="col-md-12">
                            <form action="{{ route('cabinet.wms.boschImport') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <input type="file" required placeholder="Укажите файл" name="file" class="form-control">
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">Загрузить!</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div id="cabinet_whc_bosch" class="card-body">
                    <h4>Список инвойс</h4>
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Артикул</th>
                            <th>Кол-во стикеров</th>
                            <th>Номер инвойса</th>
                            <th>Cert-nr</th>
                            <th>Печать</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($boschs as $bosch)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $bosch->article }}</td>
                                    <td>{{ $bosch->count }}</td>
                                    <td>{{ $bosch->invoice }}</td>
                                    <td>{{ $bosch->cert }}</td>
                                    <td>
                                        <v-icon
                                            title="Распечатать стикер"
                                            middle
                                            @click="printBosch({{$bosch->id}})"
                                        >
                                            mdi-printer
                                        </v-icon>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@push('cabinet_scripts')
    <!-- DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-print@0.3.0/dist/vueprint.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <script type="text/javascript">
        jQuery(document).ready(function($){
            $("#example1").DataTable({
                "responsive": true,
                "autoWidth": false,
                "pageLength": 10,
                "bLengthChange": false,
                "bFilter": true,
                "language": {
                    "url": "/dist/Russian.json"
                },
                //dom: 'Bfrtip',
                /*buttons: [
                    /!*'copy', 'csv', *, 'pdf', 'print'*!/
                    { extend: 'excel', text: 'Экспорт в эксель' }
                ]*/
            });
        });
    </script>

    <script>
        new Vue({
            el: '#cabinet_whc_bosch',
            vuetify: new Vuetify(),
            data(){
                return {
                    //
                }
            },
            methods: {
                printBosch(id){
                    axios.get(`/cabinet/wms/bosch/invoice/${id}/print`)
                        .then(res => {
                            console.log(res)
                        })
                        .catch(err => {
                            console.log(err)
                        })
                }
            }
        })
    </script>
@endpush
