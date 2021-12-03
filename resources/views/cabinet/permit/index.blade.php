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
                            <h3 class="card-title">Список пропусков</h3>
                        </div>

                        <div class="col-6 text-right">
                            <a href="{{ route('cabinet.permits.create') }}" class="btn btn-dark">Добавить</a>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>ФИО водителя</th>
                            <th>Гос.номер</th>
                            <th>Тех.паспорт</th>
                            <th>Операция</th>
                            <th>Дата планируемого заезда</th>
                            <th>Дата заезда</th>
                            <th>Дата выезда</th>
                            <th>Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($permits as $permit)
                            <tr>
                                <td>{{$permit->id}}</td>
                                <td>{{$permit->last_name}}</td>
                                <td>{{$permit->gov_number}}</td>
                                <td>{{$permit->tex_number}}</td>
                                <td>
                                    @if($permit->operation_type == 1)
                                        Погрузка
                                    @elseif($permit->operation_type == 2)
                                        Разгрузка
                                    @else
                                        Другие действие
                                    @endif
                                </td>
                                <td>
                                    @if(empty($permit->planned_arrival_date))
                                        В любое время
                                    @else
                                        {{ Carbon\Carbon::parse($permit->planned_arrival_date)->format('d.m.Y / H:i:s') }}
                                    @endif
                                </td>
                                <td>{{ $permit->date_in }}</td>
                                <td>{{ $permit->date_out }}</td>
                                <td></td>
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

    <script>
        $(function () {
            $("#example1").DataTable({
                "responsive": true,
                "autoWidth": false,
                "pageLength": 25,
                "language": {
                    "url": "/dist/Russian.json"
                },
            });
        });
    </script>
@endpush
