@extends('admin.admin')
@push('admin_styles')

    <!-- Select2 -->
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

    <!-- DataTables -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/css/report_menu.css">
@endpush
@section('content')
    <div id="adApp" class="row">

        <div class="col-12">
            <div v-if="toastSuccess" aria-live="polite" aria-atomic="true" style="position: relative; z-index: 999999;">
                <div class="toast" style="position: absolute; top: 0; right: 0;background-color: red; opacity: 1; width: 500px;color: white;">
                    <div class="toast-header">
                        <strong class="mr-auto">Admin</strong>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <span @click="toastSuccess=false" aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="toast-body" v-html="info"></div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Ashana: Перебит талонов сотрудников с одной компании на другой за определенный период</h4>
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
                                                <v-autocomplete
                                                    :items="companies"
                                                    :hint="`${companies.id}, ${companies.short_en_name}`"
                                                    item-value="id"
                                                    v-model="company_id"
                                                    item-text="short_en_name"
                                                    label="Выберите компанию"
                                                    @change="getEmployees()"
                                                ></v-autocomplete>
                                            </v-col>

                                            <v-col md="9">
                                                <v-autocomplete
                                                    :items="employees"
                                                    :hint="`${employees.id}, ${employees.full_name}`"
                                                    item-value="id"
                                                    v-model="selectedEmployees"
                                                    item-text="full_name"
                                                    label="Выберите сотрудников"
                                                    multiple
                                                ></v-autocomplete>
                                            </v-col>

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
                                                    :items="companies"
                                                    :hint="`${companies.id}, ${companies.short_en_name}`"
                                                    item-value="id"
                                                    v-model="to_company_id"
                                                    item-text="short_en_name"
                                                    label="Выберите компанию"
                                                ></v-autocomplete>
                                            </v-col>

                                            <v-col md="3">
                                                <v-btn type="button" @click="changeAshana()" class="primary">Перебит</v-btn>
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

    <!-- Select2 -->
    <script src="/plugins/select2/js/select2.full.min.js"></script>

    <script>
        new Vue({
            el: '#adApp',
            vuetify: new Vuetify(),
            data(){
                return {
                    company_id: 0,
                    to_company_id: 0,
                    search: '',
                    searchInput: '',
                    errors: [],
                    companies: <?php echo json_encode($as_companies); ?>,
                    lists: [],
                    employees: [],
                    selectedEmployees: [],
                    headers: [
                        { text: 'ID', value: 'id' },
                        { text: 'Фамилия', value: 'lastname' },
                        { text: 'Имя', value: 'firstname' },
                        { text: 'Отчество', value: 'patronymic' },
                        { text: 'Должность', value: 'pos_name' },
                        { text: 'Подразделение', value: 'dep_name' },
                        { text: 'Компания', value: 'company' },
                        { text: 'Группа', value: '' },
                        { text: 'QR', value: 'uuid' },
                        { text: 'Фото', value: 'img' },
                    ],
                    from_date: new Date().toISOString().substr(0, 10),
                    to_date: new Date().toISOString().substr(0, 10),
                    menu2: false,
                    menu3: false,
                    toastSuccess: false,
                    info: 'Успешно обновлено'
                }
            },
            methods: {
                getEmployees(){
                    axios.get(`/admin/ashana/${this.company_id}/get-employees`)
                        .then(res => {
                            console.log(res.data);
                            this.employees = res.data;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                changeAshana(){
                    let formData = new FormData();
                    formData.append('employees', this.selectedEmployees);
                    formData.append('from_date', this.from_date);
                    formData.append('to_date', this.to_date);
                    formData.append('to_company_id', this.to_company_id);

                    axios.post('/admin/ashana/change-ashana-logs', formData)
                    .then(res => {
                        this.toastSuccess = true;
                        this.info = res.data;
                        this.company_id = 0;
                        this.to_company_id = 0;
                        this.selectedEmployees = [];
                        this.from_date = new Date().toISOString().substr(0, 10);
                        this.to_date = new Date().toISOString().substr(0, 10);
                    })
                    .catch(err => {
                        console.log(err);
                    })
                },
                toExcel() {
                    let file_name = 'wcl_' + Date.now() + '.xlsx';
                    if(this.company_id !== 0) {
                        let company = this.companies.find(item => item.id === this.company_id);
                        file_name = company.short_en_name + '_' + Date.now() + '.xlsx';
                    }
                    var workbook = XLSX.utils.table_to_book(document.getElementById('printTable'));
                    XLSX.writeFile(workbook, file_name);
                }
            }
        })
    </script>

    <script>
        $(function () {
            //Initialize Select2 Elements
            //Initialize Select2 Elements
            $('.js-example-basic-multiple').select2({
                theme: 'bootstrap4',
                tags: "true",
                placeholder: "Выберите",
                allowClear: true,
                closeOnSelect: false,
            });

            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@endpush
