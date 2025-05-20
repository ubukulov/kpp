@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- DataTables -->

    @include('cabinet.inc.vue-styles')

    <style>
        .dataTables_filter input {
            margin-left: 0 !important;
            display: block !important;
            width: 100% !important;
        }
        .hidden{
            display: none;
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
                            <h3 class="card-title">BOSCH. Invoices</h3>
                        </div>

                        <div class="col-6 text-right">

                        </div>

                        <div class="col-md-12">
                            <form action="{{ route('cabinet.wms.boschImport') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <input type="file" required placeholder="Укажите файл" name="file" class="form-control">
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">Загрузить!</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div id="cabinet_whc_bosch" class="card-body">
                    <h4>Список инвойс</h4>
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Номер инвойса</th>
                            <th>Дата</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($arr as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item['invoice'] }}</td>
                                    <td>{{ $item['date'] }}</td>
                                    <td>
                                        <button @click="showTable({{$item['invoice']}})" id="btn{{$item['invoice']}}" type="button" class="btn btn-success">+</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @foreach($arr as $item)
                    @if(count($item['items'])  > 0)
                        <table id="table{{$item['invoice']}}" class="table table-bordered table-striped hidden">
                            <th></th>
                            <th>№</th>
                            <th>Артикуль</th>
                            <th>Кол-во этикетов</th>
                            <th>Cert</th>
                            <th></th>
                            @foreach($item['items'] as $ss)
                                <tr>
                                    <td></td>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $ss->article }}</td>
                                    <td>{{ $ss->count }}</td>
                                    <td>{{ $ss->cert }}</td>
                                    <td>
                                        <v-icon
                                            title="Распечатать стикер"
                                            middle
                                            @click="printBosch({{$ss->id}})"
                                        >
                                            mdi-printer
                                        </v-icon>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                    @endforeach
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@push('cabinet_scripts')

    @include('cabinet.inc.vue-scripts')

    <script>
        new Vue({
            el: '#cabinet_whc_bosch',
            vuetify: new Vuetify(),
            data(){
                return {
                    //
                }
            },
            methods: {
                printBosch(id){
                    axios.get(`/cabinet/wms/bosch/invoice/${id}/print`)
                        .then(res => {
                            console.log(res)
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                showTable(id){
                    if($('#table'+id).hasClass('hidden')) {
                        $('#btn' + id).html('-');
                        $('#table'+id).removeClass('hidden');
                    } else {
                        $('#btn' + id).html('+');
                        $('#table'+id).addClass('hidden');
                    }
                }
            }
        })
    </script>
@endpush
