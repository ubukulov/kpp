@extends('admin.admin')
@push('admin_styles')
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
                            <h3 class="card-title">Список гостевых машин</h3>
                        </div>

                        <div class="col-6 text-right">
                            <a href="{{ route('admin.white-cars.guest.create') }}" class="btn btn-dark">Добавить</a>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Клиент</th>
                            <th>Гос.номер</th>
                            <th>ФИО</th>
                            <th>Цель</th>
                            <th>Дата (планируемый)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($white_car_lists as $wcl)
                            <tr>
                                <td>{{ $wcl->id }}</td>
                                <td>{{ $wcl->company }}</td>
                                <td>{{ $wcl->gov_number }}</td>
                                <td>{{ $wcl->last_name }}</td>
                                <td>{{ $wcl->note }}</td>
                                <td>{{ $wcl->planned_arrival_date }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Клиент</th>
                            <th>Гос.номер</th>
                            <th>ФИО</th>
                            <th>Цель</th>
                            <th>Дата (планируемый)</th>
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

@push('admin_scripts')
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
                order: [[0, 'desc']],
            });
        });

        function deleteItem(id){
            if(confirm("Вы уверены, что хотите удалить?")) {
                window.location.href = "/admin/white-car-list/" + id;
            }
        }
    </script>
@endpush
