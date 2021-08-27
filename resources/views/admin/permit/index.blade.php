@extends('admin.admin')
@push('admin_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@endpush
@section('content')
    <div class="row" id="adm_employee">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="card-title">Список пропусков</h3>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Компания</th>
                            <th>Номер машины</th>
                            <th>Вх.контейнер</th>
                            <th>Исх.контейнер</th>
                            <th>Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($permits as $permit)
                        <tr>
                            <td>{{ $permit->id }}</td>
                            <td>{{ $permit->last_name }}</td>
                            <td>
                                {{ $permit->company }}
                            </td>
                            <td>
                                {{ $permit->gov_number }}
                            </td>
                            <td>
                                {{ $permit->incoming_container_number }}
                            </td>

                            <td>
                                {{ $permit->outgoing_container_number }}
                            </td>
                            <td>
                                <a href="{{ route('admin.permits.edit', ['permit' => $permit->id]) }}">Ред.</a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Компания</th>
                            <th>Номер машины</th>
                            <th>Вх.контейнер</th>
                            <th>Исх.контейнер</th>
                            <th>Действие</th>
                        </tr>
                        </tfoot>
                    </table>

                    {{ $permits->links() }}
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
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

    <script>
        $(function () {
            $("#example1").DataTable({
                "responsive": true,
                "autoWidth": false,
                "bPaginate": false,
                "info": false,
                "language": {
                    "url": "/dist/Russian.json"
                }
            });
        });
    </script>
@endpush
