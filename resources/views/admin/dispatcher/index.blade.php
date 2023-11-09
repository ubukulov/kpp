@extends('admin.admin')
@push('admin_styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
@endpush
@section('content')
    <div class="row" id="adm_dispatcher">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Список оповещения</h3>
                        </div>

                        <div class="col-6 text-right">
                            <a href="{{ route('admin.dispatcher.list.create') }}" class="btn btn-dark">Добавить</a>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="adm_dispatcher_table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Тип</th>
                            <th>SMS</th>
                            <th>Voice</th>
                            <th>WhatsApp</th>
                            <th>Интервал</th>
                            <th>Дата</th>
                            <th>Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($alertItems as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->alert->title }}</td>
                                    <td>
                                        @if($item->sms == 1)
                                            <i style="color: green;" class="fa fa-check-circle" aria-hidden="true"></i>
                                        @else
                                            <i style="color: red;" class="fa fa-stop-circle" aria-hidden="true"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->voice == 1)
                                            <i style="color: green;" class="fa fa-check-circle" aria-hidden="true"></i>
                                        @else
                                            <i style="color: red;" class="fa fa-stop-circle" aria-hidden="true"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->whatsapp == 1)
                                            <i style="color: green;" class="fa fa-check-circle" aria-hidden="true"></i>
                                        @else
                                            <i style="color: red;" class="fa fa-stop-circle" aria-hidden="true"></i>
                                        @endif
                                    </td>
                                    <td>{{ $item->interval }}</td>
                                    <td>{{ $item->created_at->format('d.m.Y H:i:s') }}</td>
                                    <td>
                                        <a href="{{ route('admin.dispatcher.alerts.edit', ['alert' => $item->id]) }}">
                                            <i class="fa fa-edit" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Тип</th>
                            <th>Способы</th>
                            <th>Интервал</th>
                            <th>Дата</th>
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
            $("#adm_dispatcher_table").DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "/dist/Russian.json"
                }
            });
        });
    </script>

    <script>
        new Vue({
            el: "#adm_dispatcher",
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
