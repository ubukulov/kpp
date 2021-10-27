@extends('layouts.app')
@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')

                <div class="row">
                    <div class="col-md-3">
                        <a class="btn btn-success" href="{{ route('o.create.task') }}">Создать заявку</a>
                    </div>

                    <div class="col-md-6">
                        <p>Пользователь: <strong>{{ Auth::user()->full_name }}</strong></p>
                    </div>

                    <div class="col-md-3 text-right">
                        <a class="btn btn-primary" href="{{ route('logout') }}">Выйти из аккаунта</a>
                    </div>
                </div>
                <br><br>

                <kt-operator-task></kt-operator-task>
            </div>
        </div>
    </div>
@stop
@push('scripts')
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
                "bPaginate": false,
                "info": false,
                "language": {
                    "url": "/dist/Russian.json"
                },
            });
        });
    </script>
@endpush
