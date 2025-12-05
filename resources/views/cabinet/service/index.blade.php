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
                            <h3 class="card-title">Заявки в техслужбу</h3>
                        </div>

                        <div class="col-3 text-right">
                            <a href="{{ asset('files/ЛК.Инструкция по созданию заявки в Техслужбу.pdf') }}" target="_blank">
                                <i class="fa fa-download"></i>&nbsp;Скачать инструкцию
                            </a>
                        </div>
                        <div class="col-3 text-right">
                            <a href="{{ route('cabinet.service.create') }}" class="btn btn-outline-primary">Создать заявку</a>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>№ заявки</th>
                            <th>Тема заявки и инициатор</th>
                            <th>Описание заявки</th>
                            <th>Статус</th>
                            <th>Решение</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($service_requests as $service_request)
                            <tr>
                                <td>Заявка №{{ $service_request->id }}<br> от {{ date('d.m.Y H:i', strtotime($service_request->created_at)) }}</td>
                                <td>{{ $service_request->service->name }} <br>({{ $service_request->user->full_name }})</td>
                                <td>{!! $service_request->comment !!}</td>
                                <td>
                                    {{ $service_request->status->name }}
                                </td>
                                <td>{{ $service_request->decision }}</td>
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
@endpush
