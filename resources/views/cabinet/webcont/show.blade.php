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
                            <h3 class="card-title">№Заявка: {{ $container_task->getNumber() }}</h3>
                        </div>

                        <div class="col-6 text-right">
                            <a class="btn btn-default" href="{{ route('cabinet.webcont.index') }}">
                                <i class="fa fa-arrow-alt-circle-left"></i>&nbsp;Назад
                            </a>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div id="cabinet_whc" class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>№Контейнер</th>
                            <th>Статус</th>
                            <th>Зона</th>
                            <th>Адрес</th>
                            <th>Дата</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($import_logs as $im)
                            <tr>
                                <td>{{ $im->container_number }}</td>
                                <td>
                                    @if($container_task->task_type == 'receive')
                                        @if($im->state == 'posted')
                                            <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ __('words.posted') }}
                                        @else
                                            <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp; {{ __('words.not_posted') }}
                                        @endif

                                        @if(isset($im->position['cancel']))
                                            <br><span>Находиться в процессе отмены</span>
                                        @endif

                                        @if(isset($im->position['edit']))
                                            <br><span>Находиться в процессе редактирование</span>
                                        @endif
                                    @endif

                                    @if($container_task->task_type == 'ship')
                                        @if($im->state == 'selected')
                                            <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ __('words.selected') }}
                                        @elseif($im->state == 'issued')
                                            <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ __('words.issued') }}
                                        @else
                                            <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp; {{ __('words.not_selected') }}
                                        @endif

                                        @if(isset($im->position['cancel']))
                                            <br><span>Находиться в процессе отмены</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    {{ $im->getZone() }}
                                </td>
                                <td>
                                    @if($container_task->isOpen())
                                        {{ $im->getContainerAddress($container_task->id) }}
                                    @elseif($container_task->task_type == 'receive' && $container_task->kind == 'common')
                                        {{ $im->getContainerAddress($container_task->id) }}
                                    @elseif($container_task->task_type == 'receive' && $container_task->kind == 'automatic')
                                        {{ $im->getContainerAddress($container_task->id) }}
                                    @else

                                    @endif
                                </td>
                                <td>{{ $im->updated_at->format('d.m.Y H:i:s') }}</td>
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
