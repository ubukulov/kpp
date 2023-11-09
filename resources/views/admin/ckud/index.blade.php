@extends('admin.admin')
@push('admin_styles')
    <!-- DataTables -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/css/report_menu.css">
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>CKUD: форма</h4>
                </div>
                <!-- /.card-header -->
                <div id="adApp" class="card-body">
                    <v-app>
                        <v-main>
                            <v-container>
                                <template>
                                    <v-container>
                                        <v-row>
                                            <v-col md="6">
                                                <v-autocomplete
                                                    :items="companies"
                                                    :hint="`${companies.id}, ${companies.short_en_name}`"
                                                    item-value="id"
                                                    v-model="company_id"
                                                    item-text="short_en_name"
                                                ></v-autocomplete>
                                            </v-col>

                                            <v-col md="4">
                                                <v-btn type="button" @click="getUsers()" class="primary">Показать</v-btn>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </template>

                                <template>
                                    <v-card>
                                        <v-card-title>
                                            Список сотрудников
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
                                            :items="lists"
                                            :search="search"
                                            id="printTable"
                                            disable-pagination
                                            hide-default-footer
                                        >
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
                    lists: [],
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
                }
            },
            methods: {
                getUsers(){
                    axios.get(`/admin/ckud/${this.company_id}/get-users`)
                        .then(res => {
                            console.log(res.data);
                            this.lists = res.data;
                        })
                        .catch(err => {
                            console.log(err)
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
@endpush
