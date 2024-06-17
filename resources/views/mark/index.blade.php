@extends('layouts.app')
@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <style>
        .kt_header {
            height: 120px;
        }
    </style>
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
                            <p style="margin-bottom: 0px;"><strong>{{ Auth::user()->full_name }}</strong></p>
                            <p style="margin-bottom: 0px;"><a href="{{ route('logout') }}">Выйти</a></p>
                        </div>
                    </div>
                    <a style="margin-bottom: 10px;" class="btn btn-success" href="{{ route('mark.create') }}">Заказ на маркировку</a>
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
                                @foreach($marking as $mark)
                                <tr>
                                    <td>{{ $mark->id }}</td>
                                    <td>{{ $mark->user->full_name }}</td>
                                    <td>
                                        @if($mark->status == 'new')
                                            Новый <a href="{{ route('mark.show', ['id' => $mark->id]) }}"><i class="fa fa-history"></i></a>
                                        @elseif($mark->status == 'processing')
                                            Выполняется <a href="{{ route('mark.show', ['id' => $mark->id]) }}"><i class="fa fa-history"></i></a>
                                        @elseif($mark->status == 'completed')
                                            Выполнен <a href="{{ route('mark.show', ['id' => $mark->id]) }}"><i class="fa fa-history"></i></a>
                                        @else
                                            Отменен <a href="{{ route('mark.show', ['id' => $mark->id]) }}"><i class="fa fa-history"></i></a>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ $mark->upload_file }}" target="_blank">Скачать</a>
                                    </td>
                                    <td>{{ $mark->created_at->format('d.m.Y H:i:s') }}</td>
                                </tr>
                                @endforeach
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
