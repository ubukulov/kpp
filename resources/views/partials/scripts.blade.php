<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
    new Vue({
        el: '#app',
        vuetify: new Vuetify(),
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
            showUser(id){
                this.propusk_id = id
                axios.get('/get-user-info/'+id)
                    .then(res => {
                        this.driver = res.data
                        this.mark_car = res.data.mark_car
                        this.date_in = res.data.date_in
                        this.gov_number = res.data.gov_number
                        this.pr_number = (res.data.pr_number) ? res.data.pr_number : '-'
                        this.last_name = res.data.last_name
                        this.date_out = res.data.date_out
                        this.company = res.data.company
                        if(res.data.operation_type == 1) {
                            this.operation_type = 'Погрузка';
                        } else if(res.data.operation_type == 2) {
                            this.operation_type = 'Разгрузка';
                        } else {
                            this.operation_type = 'Другие действие';
                        }
                        this.phone = res.data.phone
                        this.dialog = true
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
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
            print_r(id){
                alert(id)
                // axios.get('/command/print/'+id)
                // .then(res => {
                //     console.log(res)
                // })
                // .catch(err => {
                //     console.log(err)
                // })
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
<script type="text/javascript">
    function no_cirilic(input){
        let re = /[а-яё\. ]/gi;
        input.value = input.value.replace(re, '')
    }
</script>
