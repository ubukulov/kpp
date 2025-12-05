@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@endpush
@section('content')
    <div class="row" id="emp">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Асхана. Список исключение</h3>
                        </div>

                        <div class="col-6">
                            <a href="{{ route('cabinet.ashana.exception.create') }}" class="btn btn-outline-info">Добавить</a>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>ФИО сотрудника</th>
                            <th>Должность</th>
                            <th>Дата</th>
                            <th>Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($user_exceptions as $user_exception)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user_exception->full_name }}</td>
                                <td>
                                    {{ $user_exception->position_name }}
                                </td>
                                <td>
                                    {{ $user_exception->created_at }}
                                </td>
                                <td>
                                    <button onclick="deleteItem({{$user_exception->id}})" type="button" class="btn btn-outline-danger">
                                        <i class="nav-icon fas fa-cut"></i>&nbsp;Удалить
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Компания</th>
                            <th>Тип обеда</th>
                            <th>Дата</th>
                            <th>Действие</th>
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

        function deleteItem(id){
            if(confirm("Вы уверены, что хотите удалить?")) {
                window.location.href = "/cabinet/ashana/exceptions/" + id + "/delete";
            }
        }
    </script>
@endpush
