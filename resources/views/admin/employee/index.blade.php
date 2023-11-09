@extends('admin.admin')
@push('admin_styles')
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
                            <h3 class="card-title">Список пользователей</h3>
                        </div>

                        <div class="col-6 text-right">
                            <a href="{{ route('employee.create') }}" class="btn btn-dark">Добавить</a>
                            &nbsp;&nbsp;
                            <button @click="printBadge()" type="button" class="btn btn-success">Бейджик</button>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">

                    @include('partials.emp_filters')

                    <table id="emp_table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Компания</th>
                            <th>Отдел</th>
                            <th>ИИН</th>
                            <th>Должность</th>
                            <th>Статус</th>
                            <th>Бейджик</th>
                            <th>Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <th><input v-model="ids" name="{{ $employee->id }}" value="{{ $employee->id }}" type="checkbox" ></th>
                            <td>{{ $employee->id }}</td>
                            <td>{{ $employee->full_name }}</td>
                            <td>
                                {{ $employee->short_ru_name }}
                            </td>
                            <td>
                                {{ $employee->department_name }}
                            </td>
                            <td>
                                {{ $employee->iin }}
                            </td>
                            <td>
                                {{ $employee->position_name }}
                            </td>
                            <td>
                                    {{ trans("words.".$employee->status) }}
                            </td>
                            <td>
                                @if(strlen($employee->uuid) == 7)
                                    <i title="Новый бейджик" style="font-size: 20px; color: green;" class="fa fa-id-card" aria-hidden="true"></i> &nbsp;
                                @endif
                                <a target="_blank" href="{{ route('admin.employee.badge', ['id' => $employee->id]) }}">
                                    <i class="fa fa-print"></i>
                                </a>
                                &nbsp;&nbsp;

                                @if($employee->badge == 1)
                                    <i style="font-size: 20px; color: green;" class="fa fa-check-circle"></i>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('employee.edit', ['employee' => $employee->id]) }}">Ред.</a>
                                &nbsp;&nbsp;
                                <button type="button" class="btn btn-success" @click="authByUser({{ $employee->id }})">Войти</button>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Компания</th>
                            <th>Отдел</th>
                            <th>ИИН</th>
                            <th>Должность</th>
                            <th>Статус</th>
                            <th>Бейджик</th>
                            <th>Действие</th>
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

@push('admin_scripts')
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
                }
            });

            $.fn.dataTable.ext.search.push(
                function( settings, searchData, index, rowData, counter ) {

                    var offices = $('input:checkbox[name="emp_status"]:checked').map(function() {
                        return this.value;
                    }).get();


                    if (offices.length === 0) {
                        return true;
                    }

                    if (offices.indexOf(searchData[7]) !== -1) {
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

    <script>
        new Vue({
            el: "#adm_employee",
            data() {
                return {
                    ids: [],
                }
            },
            methods: {
                printBadge(){
                    console.log("ids: ",this.ids);
                    if(this.ids.length != 0) {
                        window.open('/admin/employee/badges/'+this.ids.join(), '_blank');
                    }
                },
                authByUser(id){
                    window.open('/admin/employee/' + id + '/auth' , '_blank');
                }
            },
            created(){
                console.log("ids: ",this.ids)
            }
        });
    </script>
@endpush
