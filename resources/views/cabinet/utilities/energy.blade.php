@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@endpush
@section('content')
    <div class="row" id="emp">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Текущая информация</h3>
                        </div>

                        <div class="col-6 text-right">

                        </div>
                    </div>

                </div>
                <!-- /.card-header -->

                <div class="card-body">

                    <table id="emp_table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Учётный параметр</th>
                            <th>Модель ПУ</th>
                            <th>Название ТУ</th>
                            <th>Номер ПУ</th>
                            <th>Время получения</th>
                            <th>Показания счетчика</th>
                            <th>Коэффицент</th>
                            <th>Общее потребление</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($records as $record)
                            @if($record->personalAccount == Auth::user()->company->bin)
                            <tr>
                                <td>{{ $record->metringParametr }}</td>
                                <td>{{ $record->metringDevice }}</td>
                                <td>{{ $record->namePoint }}</td>
                                <td>{{ $record->serialNumberMetringDevice }}</td>
                                <td>{{ $record->endTlm }}</td>
                                <td>{{ \Carbon\Carbon::parse($record->timeStamp)->setTimezone('Asia/Aqtobe')->format('d.m.Y, H:i') }}</td>
                            </tr>
                            @endif
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>Учётный параметр</th>
                            <th>Модель ПУ</th>
                            <th>Название ТУ</th>
                            <th>Номер ПУ</th>
                            <th>Время получения</th>
                            <th>Показания счетчика</th>
                            <th>Коэффицент</th>
                            <th>Общее потребление</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Отчет за период:</h3>
                        </div>

                        <div class="col-6 text-right">

                        </div>
                    </div>

                </div>
                <!-- /.card-header -->

                <div class="container" style="margin-left: 10px; margin-top: 20px;">
                    <form action="{{ route('cabinet.utilities.energy') }}" method="GET">
                        <div class="row">
                            <div class="col-md-2 text-left">
                                <input type="date" name="start_date" @isset($_GET['start_date']) value="{{ $_GET['start_date'] }}" @else value="<?=date('Y-m-d')?>" @endisset/>
                            </div>
                            <div class="col-md-2 text-left">
                                <input type="date" name="end_date" @isset($_GET['end_date']) value="{{ $_GET['end_date'] }}" @else value="<?=date('Y-m-d')?>" @endisset/>
                            </div>
                            <div class="col-md-2 text-left">
                                <button type="submit" class="btn btn-outline-primary">Получить</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    @if(!empty($records_for_period))
                        <table id="emp_table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Наименование организации</th>
                                <th>Номер ПУ</th>
                                <th>Начало</th>
                                <th>Конец</th>
                                <th>Расход</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($records_for_period as $record)
                                @if($record->personalAccount == Auth::user()->company->bin)
                                    <tr>
                                        <td>{{ $record->counterAgentName }}</td>
                                        <td>{{ $record->serialNumberMetringDevice }}</td>
                                        <td>{{ $record->startTlm }}</td>
                                        <td>{{ $record->endTlm }}</td>
                                        <td>{{ $record->consumption }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Наименование организации</th>
                                <th>Номер ПУ</th>
                                <th>Начало</th>
                                <th>Конец</th>
                                <th>Расход</th>
                            </tr>
                            </tfoot>
                        </table>
                    @endif
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
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

    <script>
        $(function () {
            $("#emp_table").DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "/dist/Russian.json"
                },
            });

            $.fn.dataTable.ext.search.push(
                function( settings, searchData, index, rowData, counter ) {

                    var offices = $('input:checkbox[name="emp_status"]:checked').map(function() {
                        return this.value;
                    }).get();


                    if (offices.length === 0) {
                        return true;
                    }

                    if (offices.indexOf(searchData[5]) !== -1) {
                        return true;
                    }

                    return false;
                }
            );

            var table = $('#emp_table').DataTable();

            $('input:checkbox').on('change', function () {
                table.draw();
            });
        });
    </script>
@endpush
