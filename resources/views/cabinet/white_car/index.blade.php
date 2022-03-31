@extends('cabinet.cabinet')
@push('cabinet_styles')
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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Список белых машин</h3>
                        </div>

                        <div class="col-6 text-right">
                            <a href="{{ route('cabinet.white-car-list.create') }}" class="btn btn-dark">Добавить</a>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div id="cabinet_whc" class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Гос.номер</th>
                            <th>Клиент</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th colspan="2">Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($white_car_lists as $wcl)
                        <tr>
                            <td>{{ $wcl->id }}</td>
                            <td>{{ $wcl->gov_number }}</td>
                            <td>{{ $wcl->short_ru_name }}</td>
                            <td>
                                @if($wcl->status == 'ok')
                                    <i style="font-size: 20px; color: green;" class="fa fa-check-circle"></i> Доступ разрешен
                                @else
                                    <i style="font-size: 20px; color: #cc0000;" class="fa fa-minus-circle"></i> Доступ запрещен
                                @endif
                            </td>
                            <td>{{ date('d.m.Y', strtotime($wcl->created_at)) }}</td>
                            <td>
                                <a class="btn btn-success" href="{{ route('cabinet.white-car-list.edit', ['white_car_list' => $wcl->id]) }}">
                                    <i class="nav-icon fas fa-edit"></i>&nbsp;Изменить
                                </a>
                            </td>
                            <td>
                                <button @click="areYouSure({{$wcl->id}})" type="button" class="btn btn-danger">
                                    <i class="nav-icon fas fa-times"></i>&nbsp;&nbsp;Удалить
                                </button>
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
    <script src="/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>

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
            });
        });
    </script>

    <script>
        new Vue({
            el: '#cabinet_whc',
            data () {
                return {
                    //
                }
            },
            methods: {
                areYouSure(id){
                    if(confirm('Вы уверены?')){
                        window.location.href = '/cabinet/white-car-list/' +id + '/destroy';
                    }
                },
            }
        });
    </script>
@endpush
