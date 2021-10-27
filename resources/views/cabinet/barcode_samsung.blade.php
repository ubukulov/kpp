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
											<option value="1">1</option>
											<option value="2">2</option>
											<option value="3">3</option>
											<option value="4">4</option>
											<option value="5">5</option>
											<option value="6">6</option>
											<option value="7">7</option>
											<option value="8">8</option>
											<option value="9">9</option>
											<option value="10">10</option>
											<option value="11">11</option>
											<option value="12">12</option>
											<option value="13">13</option>
											<option value="14">14</option>
											<option value="15">15</option>
											<option value="16">16</option>
											<option value="17">17</option>
											<option value="18">18</option>
											<option value="19">19</option>
											<option value="20">20</option>
										</select>
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
                    formData.append('count', this.count)
                    formData.append('order_code', this.order_code)
					if (this.order_code != '') {
						axios.post('/cabinet/samsung/barcode/command-print', formData)
                        .then(res => {
                            console.log(res)
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
