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
									<label>Выберите заказ</label>
                                    <div class="form-group">
                                        <v-autocomplete
                                            :search-input.sync="searchInput"
                                            :items="orders"
                                            item-value="ord_Code"
                                            v-model="order_code"
                                            item-text="ord_Code"
                                        ></v-autocomplete>
                                    </div>

                                    <div class="form-group">
										<label>Выберите количество упаковок(этикеток)</label>
										<select class="form-control" v-model="count">
                                            @for($i=1; $i<=60; $i++)
											<option value="{{$i}}">{{$i}}</option>
                                            @endfor
										</select>
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <button @click="commandPrint()" type="button" class="btn btn-success">Печать</button>
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
                    orders: [],
                    order_code: '',
                    searchInput: '',
                    count: 1
                }
            },
            methods: {
                getOrders(){
                    axios.get('/cabinet/samsung/barcode/get-orders')
                        .then(res => {
                            this.orders = res.data;
                            console.log(res.data)
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                commandPrint(){
                    let formData = new FormData();
                    formData.append('count', this.count);
                    formData.append('order_code', this.order_code);
                    formData.append('_token', $('input[name="_token"]').val());
                    const found = this.orders.find(element => element.ord_Code == this.order_code);
                    formData.append('ord_CustomerOrderCode', found.ord_CustomerOrderCode);
                    formData.append('KASPI_DO', found.KASPI_DO);
                    formData.append('KASPI_URL', found.KASPI_URL);
                    let address = this.orders.filter(item => item.ord_Code === this.order_code);
                    let direction = '';
                    if(address.length > 0) {
                        direction = address[0].address;
                    }

                    formData.append('direction', direction);

					if (this.order_code != '') {
						axios.post('/cabinet/samsung/barcode/command-print', formData)
                        .then(res => {
                            console.log(res);
                            if(found.KASPI_DO != null) {
                                window.open(found.KASPI_URL, '_blank');
                            }
                        })
                        .catch(err => {
                            console.log(err)
                        })
					}
                }
            },
            created(){
                this.getOrders();
            }
        })
    </script>
@endpush
