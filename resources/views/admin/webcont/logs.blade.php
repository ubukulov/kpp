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
                            <h3 class="card-title">Лог</h3>
                        </div>

                        <div class="col-6 text-right">

                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Номер контейнера</th>
                            <th>Пользователь</th>
                            <th>Операции</th>
                            <th>Тип техники</th>
                            <th>ИЗ</th>
                            <th>В</th>
                            <th>Состояние</th>
                            <th>Компания</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->container_number }}</td>
                                <td>{{ $log->full_name }}</td>
                                <td>
                                    @if($log->operation_type == 'incoming')
                                        На приход
                                    @elseif($log->operation_type == 'received')
                                        Размещен/перемещен
                                    @elseif($log->operation_type == 'in_order')
                                        На выдачу
                                    @elseif($log->operation_type == 'shipped')
                                        Выдан
                                    @else
                                        Удалён из stocks
                                    @endif
                                </td>
                                <td>{{ $log->technique }}</td>
                                <td>{{ $log->address_from }}</td>
                                <td>{{ $log->address_to }}</td>
                                <td>{{ $log->state }}</td>
                                <td>{{ $log->company }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{ $logs->links() }}
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
            });
        });
    </script>
@endpush
