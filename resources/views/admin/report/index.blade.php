@extends('admin.admin')
@push('admin_styles')
    <!-- DataTables -->
{{--    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">--}}
{{--    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">--}}

    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="card-title">Сформировать отчёт</h3>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div id="adApp" class="card-body">
                    <v-app>
                        <v-main>
                            <v-container>
                                <template>
                                    <v-container>
                                        <v-row>
                                            <v-col cols="12">
                                                <v-autocomplete
                                                    :items="companies"
                                                    :hint="`${companies.id}, ${companies.short_en_name}`"
                                                    item-value="id"
                                                    v-model="company_id"
                                                    item-text="short_en_name"
                                                ></v-autocomplete>
                                            </v-col>

                                            <v-col md="4">
                                                <v-menu
                                                    v-model="menu2"
                                                    :close-on-content-click="false"
                                                    :nudge-right="40"
                                                    transition="scale-transition"
                                                    offset-y
                                                    min-width="290px"
                                                >
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-text-field
                                                            v-model="from_date"
                                                            label="C"
                                                            prepend-icon="event"
                                                            readonly
                                                            v-bind="attrs"
                                                            v-on="on"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-date-picker v-model="from_date" :first-day-of-week="1" locale="ru" @input="menu2 = false"></v-date-picker>
                                                </v-menu>
                                            </v-col>

                                            <v-col md="4">
                                                <v-menu
                                                    v-model="menu3"
                                                    :close-on-content-click="false"
                                                    :nudge-right="40"
                                                    transition="scale-transition"
                                                    offset-y
                                                    min-width="290px"
                                                >
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-text-field
                                                            v-model="to_date"
                                                            label="ПО"
                                                            prepend-icon="event"
                                                            readonly
                                                            v-bind="attrs"
                                                            v-on="on"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-date-picker v-model="to_date" :first-day-of-week="1" locale="ru" @input="menu3 = false"></v-date-picker>
                                                </v-menu>
                                            </v-col>

                                            <v-col md="4">
                                                <v-btn type="button" @click="getPermits()" class="primary">Показать</v-btn>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </template>

                                <template>
                                    <v-card>
                                        <v-card-title>
                                            Список пропусков
                                            <v-spacer></v-spacer>
                                            <v-text-field
                                                v-model="search"
                                                append-icon="mdi-magnify"
                                                label="Поиск"
                                                single-line
                                                hide-details
                                            ></v-text-field>
                                            <v-spacer></v-spacer>
                                            <v-btn @click="toExcel">
                                                <v-icon
                                                    middle
                                                >
                                                    mdi-download
                                                </v-icon>
                                                Скачать
                                            </v-btn>
                                        </v-card-title>
                                        <v-data-table
                                            :headers="headers"
                                            :items="permits"
                                            :search="search"
                                            id="printTable"
                                            disable-pagination
                                            hide-default-footer
                                        >
                                            <template v-slot:item.operation_type="{item}">
                                                <span v-if="item.operation_type == 1">Погрузка</span>
                                                <span v-else-if="item.operation_type == 2">Разгрузка</span>
                                                <span v-else>Другие действие</span>
                                            </template>

                                            <template v-slot:item.lc_id="{item}">
                                                <span v-if="item.lc_id == 1">Легковые</span>
                                                <span v-else-if="item.lc_id == 2">до 10 тонн</span>
                                                <span v-else>Грузовые</span>
                                            </template>

                                            <template v-slot:item.bt_id="{item}">
                                                <span v-if="item.bt_id == 1">Рефрижератор (холод)</span>
                                                <span v-else>Обычный</span>
                                            </template>

                                            <template v-slot:item.is_driver="{item}">
                                                <span v-if="item.is_driver == 1">Водитель</span>
                                                <span v-else-if="item.is_driver == 2">Клиент</span>
                                                <span v-else>КПП</span>
                                            </template>
                                        </v-data-table>

                                    </v-card>
                                </template>
                            </v-container>
                        </v-main>
                    </v-app>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@push('admin_scripts')
    <!-- DataTables -->
    {{--<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>--}}

    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-print@0.3.0/dist/vueprint.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <script>
        new Vue({
            el: '#adApp',
            vuetify: new Vuetify(),
            data(){
                return {
                    company_id: 0,
                    search: '',
                    searchInput: '',
                    errors: [],
                    companies: <?php echo json_encode($companies); ?>,
                    permits: [],
                    from_date: new Date().toISOString().substr(0, 10),
                    to_date: new Date().toISOString().substr(0, 10),
                    headers: [
                        {
                            text: '№',
                            align: 'start',
                            sortable: false,
                            value: 'id',
                        },
                        { text: 'Компания', value: 'company' },
                        { text: 'Ф.И.О', value: 'last_name' },
                        { text: 'Марка', value: 'mark_car' },
                        { text: 'Гос.номер', value: 'gov_number' },
                        { text: 'Тех.паспорт', value: 'tex_number' },
                        { text: 'Вод.удос.', value: 'ud_number' },
                        { text: 'Номер прицепа', value: 'pr_number' },
                        { text: 'Тип операции', value: 'operation_type' },
                        { text: 'Телефон', value: 'phone' },
                        { text: 'Груз.под', value: 'lc_id' },
                        { text: 'Тип кузова', value: 'bt_id' },
                        { text: 'Оформил', value: 'is_driver' },
                        { text: 'Дата заезда', value: 'date_in' },
                        { text: 'Дата выезда', value: 'date_out' },
                    ],
                }
            },
            methods: {
                getPermits(){
                    let formData = new FormData();
                    formData.append('from_date', this.from_date);
                    formData.append('to_date', this.to_date);
                    formData.append('company_id', this.company_id);

                    axios.post('/admin/get/permits', formData)
                    .then(res => {
                        this.permits = res.data;
                    })
                    .catch(err => {
                        console.log(err)
                    })
                },
                toExcel() {
                    var workbook = XLSX.utils.table_to_book(document.getElementById('printTable'));
                    XLSX.writeFile(workbook, 'otchet.xlsx');
                }
            }
        })
    </script>
@endpush
