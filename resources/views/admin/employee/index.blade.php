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

                    <div id="emp_filters">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" checked class="form-check-input" name="emp_status" value="works" id="emp_status1">
                            <label class="form-check-label" for="emp_status1">Работает</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="emp_status" value="fired" id="emp_status2">
                            <label class="form-check-label" for="emp_status2">Уволен</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="emp_status" value="on_decree" id="emp_status3">
                            <label class="form-check-label" for="emp_status3">На декрете</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="emp_status" value="on_holiday" id="emp_status4">
                            <label class="form-check-label" for="emp_status4">В отпуске</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" name="emp_status" value="at_the_hospital" id="emp_status5">
                            <label class="form-check-label" for="emp_status5">На больничном</label>
                        </div>
                    </div>
                    <hr>


                    <table id="emp_table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Компания</th>
                            <th>Отдел</th>
                            <th>СКУД</th>
                            <th>Должность</th>
                            <th>Статус</th>
                            <th>Бейджик</th>
                            <th>Действие</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Компания</th>
                            <th>Отдел</th>
                            <th>СКУД</th>
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
{{--    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>--}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.7.16/vue.min.js" integrity="sha512-Wx8niGbPNCD87mSuF0sBRytwW2+2ZFr7HwVDF8krCb3egstCc4oQfig+/cfg2OHd82KcUlOYxlSDAqdHqK5TCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

    <script>
        $(function () {
            var table = $("#emp_table").DataTable({
                serverSide: true,
                processing: true,
                responsive: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('admin.employee.list') }}",
                    data: function (d) {
                        d.emp_status = $('input:checkbox[name="emp_status"]:checked')
                            .map(function () { return this.value })
                            .get();
                    }
                },
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false },
                    { data: 'id' },
                    { data: 'full_name', searchable: true },
                    { data: 'short_ru_name' , searchable: true },
                    { data: 'department_name' , searchable: true },
                    { data: 'skud' },
                    { data: 'position_name', searchable: true },
                    { data: 'status' },
                    { data: 'badge', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false },
                ],
                language: {
                    url: "/dist/Russian.json"
                }
            });

            $('input[class="form-check-input"]').on('change', function () {
                table.ajax.reload();
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
                    if(this.ids.length !== 0) {
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
