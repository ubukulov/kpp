@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <style>
        .dispatcher_btns {
            padding: 20px 50px;
            font-size: 20px;
            text-transform: uppercase;
        }
    </style>
@endpush
@section('content')
    <div class="row" id="emp">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Диспетчер</h3>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6 text-center">
                            <button type="button" class="btn btn-danger dispatcher_btns">Техногенная</button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-warning dispatcher_btns">Природная</button>
                        </div>
                    </div>

                    <br><br>
                    <h4>История: раннее отправленные уведомление</h4>
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Дата</th>
                            <th>Тип</th>
                            <th>SMS</th>
                            <th>Звонок</th>
                            <th>WhatsApp</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Дата</th>
                            <th>Тип</th>
                            <th>SMS</th>
                            <th>Звонок</th>
                            <th>WhatsApp</th>
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
    </script>
@endpush
