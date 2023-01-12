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
                            <h3 class="card-title">BOSCH. Pallet SSCC</h3>
                        </div>

                        <div class="col-6 text-right">
                            <a href="{{ route('cabinet.wms.generatePalletSSCC') }}" class="btn btn-info">Генерировать штрих-код</a>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div id="cabinet_whc" class="card-body">
                    <h4>Список сгенерированных Штрих-кодов</h4>
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Код</th>
                            <th>Пользователь</th>
                            <th>Дата</th>
                            <th>Печать</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($pallet_sscc as $sscc)
                            <tr>
                                <td>{{ $sscc->id }}</td>
                                <td>{{ $sscc->code }}</td>
                                <td>{{ $sscc->user->full_name }}</td>
                                <td>{{ $sscc->created_at }}</td>
                                <td>
                                    <button @click="commandPrint({{ $sscc->code }})" type="button" class="btn btn-warning">Печать</button>
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
            el: '#cabinet_whc',
            vuetify: new Vuetify(),
            data(){
                return {
                    //
                }
            },
            methods: {
                commandPrint(code){
                    let formData = new FormData();
                    formData.append('code', code);

                    axios.post('/cabinet/wms/pallet-sscc/'+code+'/print', formData)
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
