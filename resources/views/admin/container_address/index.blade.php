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
                            <h3 class="card-title">Список адресов для контейнеров</h3>
                        </div>

                        <div class="col-6 text-right">
                            <a href="{{ route('admin.container-address.create') }}" class="btn btn-dark">Добавить</a>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Названия</th>
                            <th>Зона</th>
                            <th>Ряд/консоль</th>
                            <th>Место</th>
                            <th>Ярус</th>
                            <th>Площадка</th>
                            <th>Дата</th>
                            <th>Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($container_address as $ca)
                        <tr>
                            <td>{{ $ca->id }}</td>
                            <td>{{ $ca->title }}</td>
                            <td>{{ $ca->zone }}</td>
                            <td>{{ $ca->row }}</td>
                            <td>{{ $ca->place }}</td>
                            <td>{{ $ca->floor }}</td>
                            <td>{{ $ca->space }}</td>
                            <td>{{ date('d.m.Y', strtotime($ca->created_at)) }}</td>
                            <td>
                                <a href="{{ route('admin.container-address.edit', ['container_address' => $ca->id]) }}">Ред.</a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Названия</th>
                            <th>Зона</th>
                            <th>Ряд/консоль</th>
                            <th>Место</th>
                            <th>Ярус</th>
                            <th>Площадка</th>
                            <th>Дата</th>
                            <th>Действие</th>
                        </tr>
                        </tfoot>
                    </table>

                    {{ $container_address->links() }}
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
                // "bPaginate": false,
                // "info": false,
                "language": {
                    "url": "/dist/Russian.json"
                },
            });
        });
    </script>
@endpush
