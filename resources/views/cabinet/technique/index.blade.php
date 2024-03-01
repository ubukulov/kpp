@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@endpush
@section('content')
    <div class="row" id="adm_employee">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Список машин/техников</h3>
                        </div>

                        <div class="col-6 text-right">

                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">

                    @include('partials.tech_filters')

                    <table id="tech_table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>VIN CODE</th>
                            <th>Марка</th>
                            <th>Статус</th>
                            <th>Дата</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($techniques as $tech)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $tech->vin_code }}</td>
                                <td>
                                    {{ $tech->mark }}
                                </td>
                                <td>
                                    {{ trans("words.technique_status.".$tech->status) }}
                                </td>
                                <td>
                                    {{ $tech->created_at }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>#</th>
                            <th>VIN CODE</th>
                            <th>Марка</th>
                            <th>Статус</th>
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
@stop

@push('cabinet_scripts')
    <!-- DataTables -->
    <script src="/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

    <script>
        $(function () {
            $("#tech_table").DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "/dist/Russian.json"
                }
            });

            $.fn.dataTable.ext.search.push(
                function( settings, searchData, index, rowData, counter ) {

                    var offices = $('input:checkbox[name="tech_status"]:checked').map(function() {
                        return this.value;
                    }).get();


                    if (offices.length === 0) {
                        return true;
                    }

                    if (offices.indexOf(searchData[3]) !== -1) {
                        return true;
                    }

                    return false;
                }
            );

            var table = $('#tech_table').DataTable();

            $('input:checkbox').on('change', function () {
                table.draw();
            });
        });
    </script>
@endpush
