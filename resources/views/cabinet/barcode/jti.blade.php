@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- DataTables -->
    {{--    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">--}}
    {{--    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">--}}
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <!-- Select2 -->
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <style>
        #printTable {
            max-height: 520px;
            overflow: auto;
        }
    </style>
@endpush
@section('content')
    <input type="hidden" id="report_name" value="{{ "Report_".date("H")."_".date("i")."_".date('s') }}">
    <div id="barcode_sam">
        <v-app>
            <v-main>
                <v-container>
                    <v-row>
                        <v-col cols="6">
                            <!-- general form elements -->
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Распечатать этикетки</h3>
                                </div>
                                <!-- /.card-header -->
                                <!-- form start -->
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Выберите клиента</label>
                                            <div class="form-group">
                                                <select name="client_id" v-model="client_id" @change="getClientBoxes" class="form-control select2bs4" style="width: 100%;">
                                                    @foreach($clients as $client)
                                                    <option value="{{ $client['dep_ID'] }}">{{ $client['dep_Code'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label>Выберите ячейку</label>
                                            <div class="form-group">
                                                <v-autocomplete
                                                    :items="client_boxes"
                                                    :hint="`${client_boxes.loc_SectorCode}, ${client_boxes.loc_SectorCode}`"
                                                    item-value="loc_SectorCode"
                                                    v-model="box_id"
                                                    item-text="loc_SectorCode"
                                                ></v-autocomplete>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <button @click="searchBox" type="button" class="btn btn-warning" style="margin-top: 31px;">Поиск</button>
                                            </div>
                                        </div>

                                        <div class="col-md-12">

                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label>Выберите</label>
                                        <v-autocomplete
                                            :search-input.sync="searchInput"
                                            :items="boxes"
                                            :hint="`${boxes.loc_Code}, ${boxes.loc_Code}`"
                                            item-value="loc_Code"
                                            v-model="box_codes"
                                            item-text="loc_Code"
                                            multiple
                                        ></v-autocomplete>
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <button @click="commandPrint" type="button" class="btn btn-success">Печать</button>
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </v-main>
        </v-app>
    </div>
@stop

@push('cabinet_scripts')
    <!-- DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-print@0.3.0/dist/vueprint.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <script>
        new Vue({
            el: '#barcode_sam',
            vuetify: new Vuetify(),
            data(){
                return {
                    client_boxes: [],
                    boxes: [],
                    order_code: '',
                    searchInput: '',
                    count: 1,
                    client_id: 0,
                    box_id: 0,
                    box_codes: [],
                    clients: <?php echo json_encode($clients); ?>
                }
            },
            methods: {
                searchBox(){
                    let formData = new FormData();
                    formData.append('client_id', this.client_id);
                    formData.append('box_id', this.box_id);

                    axios.post('/cabinet/barcode-for-wms-boxes', formData)
                        .then(res => {
                            this.boxes = res.data;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                getClientBoxes(){
                    axios.get('/cabinet/barcode-get-client-boxes/' + this.client_id)
                    .then(res => {
                        this.client_boxes = res.data;
                    })
                    .catch(err => {console.log(err)})
                },
                commandPrint(){
                    let client_id = this.client_id;
                    let client = this.clients.find(function(item, i, arr){
                        if(item.dep_ID == client_id) {
                            return item;
                        }
                    });

                    window.location.href = '/cabinet/jti/barcode-command-print?client='+ client.dep_Code + '&code=' + JSON.stringify(this.box_codes);
                }
            },
            created(){
                //console.log('ss',  this.clients);
            }
        })
    </script>
@endpush
