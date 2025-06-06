@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">

@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Перечень постоянных пропусков</h3>
                        </div>

                        <div class="col-6 text-right" style="display: flex; justify-content: space-between;">
                            <a href="{{ asset('files/instructions/Инструкция по оформлению постоянного пропуска ТС_КПП_ИЛЦ DAMU.pdf') }}" target="_blank">
                                <p>
                                    <i class="nav-icon fas fa-download"></i> Скачать инструкцию
                                </p>
                            </a>
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
                            <th>ФИО</th>
                            <th>Клиент</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Ред.</th>
                            <th>Удалить</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($white_car_lists as $wcl)
                        <tr>
                            <td>{{ $wcl->id }}</td>
                            <td>{{ strtoupper($wcl->gov_number) }}</td>
                            <td>{{ $wcl->full_name }}</td>
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
                        <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Гос.номер</th>
                            <th>ФИО</th>
                            <th>Клиент</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Ред.</th>
                            <th>Удалить</th>
                        </tr>
                        </tfoot>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.7.16/vue.min.js" integrity="sha512-Wx8niGbPNCD87mSuF0sBRytwW2+2ZFr7HwVDF8krCb3egstCc4oQfig+/cfg2OHd82KcUlOYxlSDAqdHqK5TCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

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
                "columns": [
                    { "searchable": false },
                    { "searchable": true },
                    { "searchable": true },
                    null,
                    null,
                    null,
                    null,
                    null
                ]
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
