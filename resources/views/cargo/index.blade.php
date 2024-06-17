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
                    <div class="col">
                        <div class="text-left">
                            <h4>Список задач</h4>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-right">
                            <a class="btn btn-info" href="{{ route('cargo.create') }}">
                                <i class="fa fa-plus"></i>&nbsp;Добавить
                            </a>
                        </div>
                    </div>

                    {{--<div class="col-md-12">
                        <div class="form-group">
                            <label style="font-size: 16px;">Фильтр:</label>
                            <select style="font-size: 20px !important;" class="form-control">
                                <option value="1">Новые Заявки</option>
                                <option value="2">В работе</option>
                                <option value="3">Закрытие Заявки</option>
                            </select>
                        </div>
                    </div>--}}

                    <div class="col-md-12">
                        <table id="emp_table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Номер заявки</th>
                                <th>Тип</th>
                                <th>Компания</th>
                                <th>Статус</th>
                                <th></th>
                                <th>Действие</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($cargo as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->getType() }}</td>
                                    <td>
                                        {{ $item->company->full_company_name }}
                                    </td>
                                    <td>
                                        {{ __('words.cargo.'.$item->status) }}
                                    </td>
                                    <td>
                                        @if($item->canClose())
                                        <a target="_blank" href="{{ route('cargo.qr', ['id' => $item->id]) }}"><i style="font-size: 30px;" class="fa fa-qrcode"></i></a>
                                        @endif
                                    </td>
                                    <td>
                                        {{--<a href="{{ route('cargo.show', ['id' => $item->id]) }}">Просмотр</a>--}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Номер заявки</th>
                                <th>Тип</th>
                                <th>Компания</th>
                                <th>Статус</th>
                                <th></th>
                                <th>Действие</th>
                            </tr>
                            </tfoot>
                        </table>

                    </div>
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
            $("#emp_table").DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "/dist/Russian.json"
                }
            });
        });
    </script>
@endpush
