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
                            <a style="margin-bottom: 10px;" class="btn btn-success" href="{{ route('mark.index') }}">Назад</a>
                        </div>

                        <div class="col-md-6">
                            <strong>Заявка: №{{ $mark->id }}</strong>
                        </div>

                        <div class="col-md-3 text-right">
                            <p style="margin-bottom: 0px;"><strong>{{ Auth::user()->full_name }}</strong></p>
                            <p style="margin-bottom: 0px;"><a href="{{ route('logout') }}">Выйти</a></p>
                        </div>
                    </div>

                </div>

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <button id="exportExcel" data-id="{{ $mark->id }}" type="button" class="btn btn-warning">Экспорт в эксель</button>
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Номер контейнера</th>
                                    <th>GTIN</th>
                                    <th>Статус</th>
                                    <th>Номер короба</th>
                                    <th>Код маркировки</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($marking_codes as $mark_code)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $mark_code->container_number }}</td>
                                        <td>{{ $mark_code->gtin }}</td>
                                        <td>
                                            @if($mark_code->status == 'not_marked')
                                                Не маркировано
                                            @else
                                                Маркировано
                                            @endif
                                        </td>
                                        <td>
                                            {{ $mark_code->box_number }}
                                        </td>
                                        <td>{{ $mark_code->marking_code }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Номер контейнера</th>
                                    <th>GTIN</th>
                                    <th>Статус</th>
                                    <th>Номер короба</th>
                                    <th>Код маркировки</th>
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

            $('#exportExcel').click(function(){
                let mark_id = $(this).data('id');
                $.ajax({
                    type: 'POST',
                    url: "/marking/generate-excel",
                    data: { mark_id: mark_id, _token: $('meta[name="csrf-token"]').attr('content')},
                    success: function(data){
                        window.location.href = data;
                    }
                });
            });
        });
    </script>
@endpush
