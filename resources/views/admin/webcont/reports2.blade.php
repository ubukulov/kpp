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
    <link rel="stylesheet" href="/css/report_menu.css">
@endpush
@section('content')
    <div id="adApp"  class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12">
                            Отчеты по крановым операциям
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <v-app>
                        <v-main>
                            <v-container>
                                <template>
                                    <v-container>
                                        <v-row>
                                            <v-col md="3">
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

                                            <v-col md="3">
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

                                            <v-col md="3">
                                                <v-autocomplete
                                                    :hint="`${reports.value}, ${reports.text}`"
                                                    :items="reports"
                                                    v-model="report_id"
                                                    item-value="value"
                                                    item-text="text"
                                                    label="Тип отчета"
                                                    required
                                                ></v-autocomplete>
                                            </v-col>

                                            <v-col md="3">
                                                <v-btn type="button" @click="getStats()" class="primary">Скачать отчет</v-btn>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </template>

                            </v-container>
                        </v-main>
                    </v-app>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12">
                            Отчет: Детализация
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <v-app>
                        <v-main>
                            <v-container>
                                <template>
                                    <v-container>
                                        <v-row>
                                            <v-col md="3">
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
                                                            label="Дата"
                                                            prepend-icon="event"
                                                            readonly
                                                            v-bind="attrs"
                                                            v-on="on"
                                                        ></v-text-field>
                                                    </template>
                                                    <v-date-picker v-model="from_date" :first-day-of-week="1" locale="ru" @input="menu2 = false"></v-date-picker>
                                                </v-menu>
                                            </v-col>

                                            <v-col md="3">
                                                <v-autocomplete
                                                    :hint="`${users.id}, ${users.full_name}`"
                                                    :items="users"
                                                    v-model="user_id"
                                                    item-value="id"
                                                    item-text="full_name"
                                                    label="Тип отчета"
                                                    required
                                                ></v-autocomplete>
                                            </v-col>

                                            <v-col md="3">
                                                <v-btn type="button" @click="getStats()" class="primary">Скачать отчет</v-btn>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </template>

                            </v-container>
                        </v-main>
                    </v-app>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@stop

@push('admin_scripts')
    <!-- DataTables -->
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
                        { text: 'Ф.И.О', value: 'full_name' },
                        { text: 'Спредер', value: 'spreder' },
                        { text: 'Автокран-наёмный', value: 'avn' },
                        { text: 'Автокран-561', value: 'av561' },
                        { text: 'Ричстакер', value: 'rich' },
                        { text: 'Кран-30', value: 'cr30' },
                        { text: 'Кран-50', value: 'cr50' },
                        { text: 'Автокран-952', value: 'av952' },
                        { text: 'Автокран-296', value: 'av296' },
                    ],
                    report_id: 0,
                    reports: [
                        { text: 'Крановщики', value: 0 },
                        { text: 'Стропальщики', value: 1}
                    ],
                    users: <?php echo json_encode($users) ?>,
                    user_id: 0
                }
            },
            methods: {
                getStats(){
                    let formData = new FormData();
                    formData.append('from_date', this.from_date);
                    formData.append('to_date', this.to_date);
                    formData.append('report_id', this.report_id);

                    axios.post('/admin/webcont/get-reports', formData)
                        .then(res => {
                            window.location.href = res.data;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                toExcel() {
                    var workbook = XLSX.utils.table_to_book(document.getElementById('printTable'));
                    XLSX.writeFile(workbook, 'otchet.xlsx');
                }
            },
            created(){
                console.log(this.users);
            }
        })
    </script>
@endpush
