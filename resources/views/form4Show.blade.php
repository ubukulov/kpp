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
                            <a style="margin-bottom: 10px;" class="btn btn-success" href="/form4">Назад</a>
                        </div>

                        <div class="col-md-6">
                            @if($id == 3)
                            <strong>Заявка: №3</strong>
                            @elseif($id==4)
                            <strong>Заявка: №4</strong>
                            @elseif($id==2)
                            <strong>Заявка: №2</strong>
                            @endif
                        </div>

                        <div class="col-md-3 text-right">
                            <p style="margin-bottom: 0px;"><strong>Тест Тест</strong></p>
                            <p style="margin-bottom: 0px;"><a href="#">Выйти</a></p>
                        </div>
                    </div>

                </div>

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>Номер контейнера</th>
                                    <th>GTIN</th>
                                    <th>Статус</th>
                                    <th>Номер короба</th>
                                    <th>Код маркировки</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($id == 3)
                                    <tr>
                                        <td>CCLU6598740</td>
                                        <td>04060519116308</td>
                                        <td>Маркировано</td>
                                        <td>00069284015648552917</td>
                                        <td>010406051911630821whWocOn87sKOH91009892RjUwYX9rjvgiUh6M6+p3i3Q4Agd8biyWx7oQ42PzdqkxeaJA9bjf3FnQ4kxKVa+hvYw10BSxhpX1uGM3xyo2OA</td>
                                    </tr>
                                    <tr>
                                        <td>CCLU6598740</td>
                                        <td>04060519116308</td>
                                        <td>Не маркировано</td>
                                        <td></td>
                                        <td>010406051911630821Wl10Ga3cO-"do91009892vLM5s45G7uL79e2CvkVpPbHUOlY6ev8SXkiqMTHujeLirkXXk5ISAK+L/34SJVr8u33utav5MqWxKvaSgtKIfw</td>
                                    </tr>
                                    <tr>
                                        <td>CCLU6598740</td>
                                        <td>04060519116308</td>
                                        <td>Не маркировано</td>
                                        <td></td>
                                        <td>010406051911630821XiaLs?FpXtoCq91009892FQlZzNI3ek38wUZqjGePepq+6i/UqScO4MvRtITEwiVylFHY4kSuHH2X735ye4vUFe+tIMH22jVIEVud2Y34JA</td>
                                    </tr>
                                    <tr>
                                        <td>CCLU6598740</td>
                                        <td>04060519116308</td>
                                        <td>Маркировано</td>
                                        <td>00069284015648552917</td>
                                        <td>010406051911630821YQ=IccNiJ*:hC91009892+SoVQIRYFf1ewvhxd49cF1GeVKN3jeXCtFLIL4tdPOq00TUCEgS2IVjxpcFK20j5X7HlUMTcf8tRCpJ/FI1wbA</td>
                                    </tr>
                                    @elseif($id==2)
                                        <tr>
                                            <td>CCLU6598740</td>
                                            <td>04060519116308</td>
                                            <td>Маркировано</td>
                                            <td>00069284015648552917</td>
                                            <td>010406051911630821whWocOn87sKOH91009892RjUwYX9rjvgiUh6M6+p3i3Q4Agd8biyWx7oQ42PzdqkxeaJA9bjf3FnQ4kxKVa+hvYw10BSxhpX1uGM3xyo2OA</td>
                                        </tr>
                                        <tr>
                                            <td>CCLU6598740</td>
                                            <td>04060519116308</td>
                                            <td>Маркировано</td>
                                            <td>00069284015648552429</td>
                                            <td>010406051911630821Wl10Ga3cO-"do91009892vLM5s45G7uL79e2CvkVpPbHUOlY6ev8SXkiqMTHujeLirkXXk5ISAK+L/34SJVr8u33utav5MqWxKvaSgtKIfw</td>
                                        </tr>
                                        <tr>
                                            <td>CCLU6598740</td>
                                            <td>04060519116308</td>
                                            <td>Маркировано</td>
                                            <td>00069284015648554263</td>
                                            <td>010406051911630821XiaLs?FpXtoCq91009892FQlZzNI3ek38wUZqjGePepq+6i/UqScO4MvRtITEwiVylFHY4kSuHH2X735ye4vUFe+tIMH22jVIEVud2Y34JA</td>
                                        </tr>
                                        <tr>
                                            <td>CCLU6598740</td>
                                            <td>04060519116308</td>
                                            <td>Маркировано</td>
                                            <td>00069284015648554737</td>
                                            <td>010406051911630821YQ=IccNiJ*:hC91009892+SoVQIRYFf1ewvhxd49cF1GeVKN3jeXCtFLIL4tdPOq00TUCEgS2IVjxpcFK20j5X7HlUMTcf8tRCpJ/FI1wbA</td>
                                        </tr>
                                    @elseif($id==4)
                                        <tr>
                                            <td>CCLU6598740</td>
                                            <td>04060519116308</td>
                                            <td>Не маркировано</td>
                                            <td></td>
                                            <td>010406051911630821whWocOn87sKOH91009892RjUwYX9rjvgiUh6M6+p3i3Q4Agd8biyWx7oQ42PzdqkxeaJA9bjf3FnQ4kxKVa+hvYw10BSxhpX1uGM3xyo2OA</td>
                                        </tr>
                                        <tr>
                                            <td>CCLU6598740</td>
                                            <td>04060519116308</td>
                                            <td>Не маркировано</td>
                                            <td></td>
                                            <td>010406051911630821Wl10Ga3cO-"do91009892vLM5s45G7uL79e2CvkVpPbHUOlY6ev8SXkiqMTHujeLirkXXk5ISAK+L/34SJVr8u33utav5MqWxKvaSgtKIfw</td>
                                        </tr>
                                        <tr>
                                            <td>CCLU6598740</td>
                                            <td>04060519116308</td>
                                            <td>Не маркировано</td>
                                            <td></td>
                                            <td>010406051911630821XiaLs?FpXtoCq91009892FQlZzNI3ek38wUZqjGePepq+6i/UqScO4MvRtITEwiVylFHY4kSuHH2X735ye4vUFe+tIMH22jVIEVud2Y34JA</td>
                                        </tr>
                                        <tr>
                                            <td>CCLU6598740</td>
                                            <td>04060519116308</td>
                                            <td>Не маркировано</td>
                                            <td></td>
                                            <td>010406051911630821YQ=IccNiJ*:hC91009892+SoVQIRYFf1ewvhxd49cF1GeVKN3jeXCtFLIL4tdPOq00TUCEgS2IVjxpcFK20j5X7HlUMTcf8tRCpJ/FI1wbA</td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot>
                                <tr>
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
        });
    </script>
@endpush
