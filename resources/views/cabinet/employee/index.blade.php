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
                            <h3 class="card-title">Список пользователей</h3>
                        </div>

                        <div class="col-6 text-right">
                            <a href="{{ route('cabinet.employees.create') }}" class="btn btn-dark">Добавить</a>
                            &nbsp;&nbsp;<button type="button" @click="printBadge()" class="btn btn-success">Распечатать</button>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th><input type="checkbox" @change="selectAll($event)"></th>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Подразделение</th>
                            <th>Статус</th>
                            <th>Бейджик</th>
                            <th>Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td><input type="checkbox" class="chs" v-model="ids" data-id="{{ $employee->id }}" value="{{ $employee->id }}"></td>
                            <td>{{ $employee->id }}</td>
                            <td>{{ $employee->full_name }}</td>
                            <td>
                                {{ $employee->department->title }}
                            </td>
                            <td>{{ trans("words.".$employee->getWorkingStatus()->status) }}</td>
                            <td>
                                <a target="_blank" href="{{ route('employee.badge', ['id' => $employee->id]) }}">
                                    <i class="fa fa-print"></i>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('cabinet.employees.edit', ['employee' => $employee->id]) }}">Ред.</a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Подразделение</th>
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
            $("#example1").DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "/dist/Russian.json"
                },
            });
        });
    </script>
    <script>
        new Vue({
            el: "#emp",
            data() {
                return {
                    ids: [],
                }
            },
            methods: {
                printBadge(){
                    console.log('pr', this.ids)
                    if(this.ids.length != 0) {
                        window.open('/cabinet/employees/badges/'+this.ids.join(), '_blank');
                    }
                },
                selectAll(event){
                    if(event.target.checked) {
                        let s_ids = [];
                        $('.chs').each(function(i, el){
                            $(el).prop("checked", true);
                            s_ids[i] = $(el).val();
                        });
                        this.ids = s_ids;
                    } else {
                        this.ids = []
                        $('.chs').each(function(i, el){
                            $(el).prop("checked", false);
                        });
                    }
                }
            },
            created(){
                console.log("ids: ",this.ids.length)
            }
        });
    </script>
@endpush
