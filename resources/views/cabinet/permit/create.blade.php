@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endpush
@section('content')
    <!-- general form elements -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Заказать пропуск</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('cabinet.permits.store') }}" method="POST" role="form">
            @csrf
            <div id="cabinet_create_permit" class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <h4 style="font-style: italic;">Данные о машине</h4>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>№тех.паспорта</label>
                                    <input onkeyup="return no_cirilic(this);" placeholder="AB020111" style="text-transform: uppercase;" v-model="tex_number" name="tex_number" tabindex="1" type="text" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4" style="padding-top: 30px;">
                                <button @click="checkCar()" type="button" class="btn btn-warning">Проверить</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Гос.номер</label>
                                    <input onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="gov_number" tabindex="2" type="text" placeholder="888BBZ05" required name="gov_number" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Номер прицепа</label>
                                    <input v-model="pr_number" tabindex="4" type="text" name="pr_number" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Марка авто</label>
                            <input v-model="mark_car" type="text" tabindex="3" name="mark_car" class="form-control">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Грузоподъемность ТС</label>
                                    <select name="lc_id" v-model="lc_id" tabindex="10" class="form-control" style="width: 100%;">
                                        @foreach($category_tc as $ct)
                                            <option value="{{ $ct->id }}">{{ $ct->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Тип кузова</label>
                                    <select name="bt_id" v-model="bt_id" tabindex="11" class="form-control" style="width: 100%;">
                                        @foreach($body_type as $bt)
                                            <option value="{{ $bt->id }}">{{ $bt->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div v-if="operation_type != 3" class="form-group">
                            <label>Название транспортной компании/частник</label>
                            <input type="text" class="form-control" required name="from_company" v-model="from_company">
                        </div>

                        <div class="form-group">
                            <label>Дата планируемого заезда</label>
                            <input tabindex="9" value="<?=date('Y-m-d H:i:s')?>" type="text" id="date_in" required name="created_at" class="form-control">
                        </div>

                    </div>

                    <div class="col-md-6">
                        <h4 style="font-style: italic;">Данные о водителе</h4>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>№вод.удостоверение</label>
                                    <input placeholder="BN203526" onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="ud_number" tabindex="5" type="text" name="ud_number" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4" style="padding-top: 30px;">
                                <button @click="checkDriver()" style="color: #fff;" type="button" class="btn btn-dark">Проверить</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>ФИО</label>
                            <input style="text-transform: uppercase;" v-model="last_name" tabindex="6" type="text" name="last_name" required class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Телефон(без пробелов с 8-ки)</label>
                            <input v-model="phone" tabindex="7" type="text" placeholder="87778882255" name="phone" required class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Вид операции</label>
                            <select tabindex="8" name="operation_type" v-model="operation_type" id="type" class="form-control">
                                <option value="1">Погрузка</option>
                                <option value="2">Разгрузка</option>
                                <option value="3">Другие действия</option>
                            </select>
                        </div>

                        <div v-if="operation_type != 3" class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Маршрут</label>
                                    <select tabindex="8" name="direction_id" v-model="direction_id" class="form-control">
                                        @foreach($directions as $direction)
                                        <option value="{{$direction->id}}">{{$direction->title}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div v-if="direction_id == 6" class="form-group">
                                    <label>Напишите</label>
                                    <input style="font-size: 16px !important;" name="to_city" type="text" required class="form-control" v-model="to_city">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Заказат</button>
            </div>
        </form>
    </div>
    <!-- /.card -->
@stop
@push('cabinet_scripts')
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="/plugins/select2/js/select2.full.min.js"></script>
    <script>
        $(function () {
            //Initialize Select2 Elements
            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
            $( "#date_in" ).daterangepicker({
                singleDatePicker: true,
                // showDropdowns: true,
                timePicker: true,
                timePicker24Hour: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss',
                    applyLabel: "OK",
                    cancelLabel: "Отмена",
                    firstDay: 1,
                    daysOfWeek: ["Вс","Пн","Вт","Ср","Чт","Пт","Сб"],
                    monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь']
                }
            });
        });
    </script>
    <script type="text/javascript">
        function no_cirilic(input){
            let re = /[а-яё\. ]/gi;
            input.value = input.value.replace(re, '')
        }
    </script>
    <script>
        new Vue({
            el: '#cabinet_create_permit',
            data () {
                return {
                    mark_car: '',
                    gov_number: '',
                    tex_number: '',
                    pr_number: '',
                    operation_type: 1,
                    last_name: '',
                    from_company: '',
                    to_city: '',
                    phone: '',
                    ud_number: '',
                    lc_id: 0,
                    bt_id: 0,
                    direction_id: 0,
                }
            },
            methods: {
                checkCar(){
                    axios.get('/get-car-info/'+this.tex_number)
                        .then(res => {
                            console.log(res)
                            this.gov_number = res.data.gov_number
                            this.mark_car = res.data.mark_car
                            this.pr_number = res.data.pr_number
                            this.lc_id = res.data.lc_id
                            this.bt_id = res.data.bt_id
                            this.from_company = res.data.from_company
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                checkDriver(){
                    axios.get('/get-driver-info/'+this.ud_number)
                        .then(res => {
                            this.last_name = res.data.fio
                            this.phone = res.data.phone
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
            }
        });
    </script>
@endpush
