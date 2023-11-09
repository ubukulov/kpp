@extends('layouts.app')
@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@endpush
@section('content')
    <div class="container">
        <div class="row">
            <div class="kt">
                <div class="col-md-12">
                    @include('partials.kt_header')

                    <div class="row">
                        <div class="col-md-3">

                        </div>

                        <div class="col-md-6">

                        </div>

                        <div class="col-md-3 text-right">
                            <p style="margin-bottom: 0px;"><strong>Тест Тест</strong></p>
                            <p style="margin-bottom: 0px;"><a href="#">Выйти</a></p>
                        </div>
                    </div>
                    <a style="margin-bottom: 10px;" class="btn btn-success" href="{{ route('form4.create') }}">Заказ на маркировку</a>
                </div>

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>Номер заявки</th>
                                    <th>Оформил</th>
                                    <th>Статус</th>
                                    <th>Файл</th>
                                    <th>Дата</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>4</td>
                                        <td>Тест Тест</td>
                                        <td>Новый <a href="{{ route('form4.show', ['id' => 4]) }}"><i class="fa fa-history"></i></a></td>
                                        <td><a href="#">Скачать</a></td>
                                        <td>07.10.2022 15:12</td>
                                    </tr>

                                    <tr>
                                        <td>3</td>
                                        <td>Тест Тест</td>
                                        <td>Выполняется <a href="{{ route('form4.show', ['id' => 3]) }}"><i class="fa fa-history"></i></a></td>
                                        <td><a href="#">Скачать</a></td>
                                        <td>07.10.2022 12:40</td>
                                    </tr>

                                    <tr>
                                        <td>2</td>
                                        <td>Тест2 Тест2</td>
                                        <td>Выполнен <a href="{{ route('form4.show', ['id' => 2]) }}"><i class="fa fa-history"></i></a></td>
                                        <td><a href="#">Скачать</a></td>
                                        <td>05.10.2022 10:34</td>
                                    </tr>

                                    <tr>
                                        <td>1</td>
                                        <td>Тест3 Тест3</td>
                                        <td>Отменен</td>
                                        <td><a href="#">Скачать</a></td>
                                        <td>02.10.2022 09:15</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>Номер заявки</th>
                                    <th>Оформил</th>
                                    <th>Статус</th>
                                    <th>Файл</th>
                                    <th>Дата</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
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
