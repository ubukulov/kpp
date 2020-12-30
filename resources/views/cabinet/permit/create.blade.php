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
                                    <label>Гос.номер(без пробелов, на анг)</label>
                                    <input onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="gov_number" tabindex="1" type="text" placeholder="888BBZ05" required name="gov_number" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4" style="padding-top: 30px;">
                                <button @click="checkCar()" type="button" class="btn btn-warning">Проверить</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>№тех.паспорта</label>
                            <input style="text-transform: uppercase;" v-model="tex_number" tabindex="2" type="text" name="tex_number" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Марка авто</label>
                            <input v-model="mark_car" type="text" tabindex="3" name="mark_car" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Номер прицепа</label>
                            <input v-model="pr_number" tabindex="4" type="text" name="pr_number" class="form-control">
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
                            <select tabindex="8" name="operation_type" id="type" class="form-control">
                                <option value="1">Погрузка</option>
                                <option value="2">Разгрузка</option>
                                <option value="3">Другие действие</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Дата планируемого заезда</label>
                            <input tabindex="9" value="<?=date('d.m.Y H:i')?>" type="text" id="date_in" required name="date_in" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Грузоподъемность ТС</label>
                            <select name="cat_tc_id" tabindex="10" class="form-control select2bs4" style="width: 100%;">
                                @foreach($category_tc as $ct)
                                    <option value="{{ $ct->id }}">{{ $ct->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Тип кузова</label>
                            <select name="body_type_id" tabindex="11" class="form-control select2bs4" style="width: 100%;">
                                @foreach($body_type as $bt)
                                    <option value="{{ $bt->id }}">{{ $bt->title }}</option>
                                @endforeach
                            </select>
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
                    format: 'DD.MM.YYYY HH:mm',
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
                    dialog: false,
                    mark_car: '',
                    gov_number: '',
                    tex_number: '',
                    company: '',
                    pr_number: '',
                    operation_type: '',
                    date_in: '',
                    date_out: '',
                    last_name: '',
                    phone: '',
                    ud_number: '',
                    driver: [],
                    propusk_id: 0,
                    is_driver: true,
                    wantToOrder: false,
                    dialog_success: false,
                    success_html: '',
                    cat_tc_id: 0,
                    body_type_id: 0,
                    png: '',
                    search: '',
                    html: '',
                }
            },
            methods: {
                checkCar(){
                    axios.get('/get-car-info/'+this.gov_number)
                        .then(res => {
                            console.log(res)
                            this.tex_number = res.data.tex_number
                            this.mark_car = res.data.mark_car
                            this.pr_number = res.data.pr_number
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                checkDriver(){
                    axios.get('/get-driver-info/'+this.ud_number)
                        .then(res => {
                            console.log(res.data.fio)
                            this.last_name = res.data.fio
                            this.phone = res.data.phone
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                closePrintForm(){
                    this.dialog = false
                    this.mark_car = ''
                    this.tex_number = ''
                    this.date_in = ''
                    this.gov_number = ''
                    this.pr_number = ''
                    this.last_name = ''
                    this.company = ''
                    this.ud_number = ''
                    this.phone = ''
                },
                isDriver(){
                    let formData = new FormData();
                    formData.append('gov_number', this.gov_number)
                    formData.append('ud_number', this.ud_number)
                    axios.post('/check-driver', formData)
                        .then(res => {
                            console.log(res)
                            this.is_driver = true
                            this.dialog_success = false
                        })
                        .catch(err => {
                            console.log(err)
                            this.png = '<img src="https://www.nicepng.com/png/full/67-677160_wrong-clip-art-clip-art.png" style="max-width: 100%; width: 100px;" />'
                            this.success_html = '<p style="font-size: 30px; line-height: 30px;">Проверка не пройдена, Вам необходимо пройти первичную регистрацию на КПП.</p>'
                            this.is_driver = false
                            this.dialog_success = true
                        })
                },
                orderPermitByDriver(){
                    let formData = new FormData();
                    formData.append('gov_number', this.gov_number)
                    formData.append('ud_number', this.ud_number)
                    formData.append('company', this.company)
                    formData.append('operation_type', this.operation_type)
                    formData.append('cat_tc_id', this.cat_tc_id)
                    formData.append('body_type_id', this.body_type_id)
                    formData.append('wantToOrder', this.wantToOrder)
                    axios.post('/order-permit-by-driver', formData)
                        .then(res => {
                            console.log(res)
                            this.png = '<img src="https://www.nicepng.com/png/detail/362-3624869_icon-success-circle-green-tick-png.png" style="max-width: 100%; width: 100px;" />'
                            this.success_html = '<p style="font-size: 30px; line-height: 30px;">Пропуск №'+res.data.id+' успешно оформлен!</p>'
                            if(this.wantToOrder) {
                                this.success_html = this.success_html + "<p style='color: #000;font-size:18px;'>Для получения информации о заказах, Вы можете написать на WhatsApp <span style='color: green !important;'>(<a style='color: green !important;' href='https://api.whatsapp.com/send?phone=77777022000' target='_blank'>8 777 702 2000</a>)</span>, либо дождаться звонка от нашего менеджера.</p>"
                            }
                            this.dialog_success = true
                            this.gov_number = ''
                            this.ud_number = ''
                            this.company = ''
                            this.operation_type = ''
                            this.is_driver = false
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                searchPermit(){
                    this.html = ''
                    let formData = new FormData();
                    formData.append('search', this.search)
                    axios.post('/search/permit', formData)
                        .then(res => {
                            console.log(res)
                            // this.html = '<tr style="background: cadetblue;">'
                            this.html = this.html + '<th scope="row">'+res.data.id+'</th>'
                            this.html = this.html + '<td>'+res.data.last_name+'</td>'
                            this.html = this.html + '<td>'+res.data.ud_number+'</td>'
                            this.html = this.html + '<td>'+res.data.company+'</td>'
                            if(res.data.operation_type == 1) {
                                this.html = this.html + '<td>Погрузка</td>'
                            } else if(res.data.operation_type == 2) {
                                this.html = this.html + '<td>Разгрузка</td>'
                            } else {
                                this.html = this.html + '<td>Другие действие</td>'
                            }
                            this.html = this.html + '<td>'+res.data.phone+'</td>'
                            this.html = this.html + '<td>'+res.data.gov_number+'</td>'
                            this.html = this.html + '<td><v-icon middle v-on:click="print_r('+res.data.id+')">mdi-printer</v-icon></td>'
                            // $("tbody").prepend(this.html)
                        })
                        .catch(err => {
                            console.log(err)
                        })
                }
            }
        });
    </script>
@endpush
