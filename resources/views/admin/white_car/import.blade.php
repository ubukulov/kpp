@extends('admin.admin')
@push('admin_styles')
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
@endpush
@section('content')
    <div class="row" id="emp_wcl">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Импорт из файла (белый список)</h3>
                        </div>

                        <div class="col-6">
                            <a href="/files/wcl_template.xlsx" target="_blank">Скачать шаблон</a>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <v-app>
                        <v-main>
                            <v-container>
                                <v-row>
                                    <v-col md="3">
                                        <v-autocomplete
                                            :search-input.sync="searchInput"
                                            :hint="`${companies.id}, ${companies.short_ru_name}`"
                                            :items="companies"
                                            v-model="company_id"
                                            item-value="id"
                                            item-text="short_ru_name"
                                            label="Укажите компанию"
                                            required
                                        ></v-autocomplete>
                                    </v-col>

                                    <v-col md="3">
                                        <v-autocomplete
                                            :hint="`${kpps.id}, ${kpps.title}`"
                                            :items="kpps"
                                            v-model="kpp_id"
                                            item-value="id"
                                            item-text="title"
                                            label="Укажите КПП"
                                            required
                                        ></v-autocomplete>
                                    </v-col>

                                    <v-col md="3">
                                        <template>
                                            <v-file-input
                                                show-size
                                                counter
                                                v-model="import_file"
                                                label="Укажите файл"
                                            ></v-file-input>
                                        </template>
                                    </v-col>

                                    <v-col md="3">
                                        <v-btn type="button" @click="executeImport()" class="primary">Запустить!</v-btn>
                                    </v-col>
                                </v-row>

                                <template>
                                    <v-card>
                                        <v-card-title>
                                            Отчет об импорте
                                        </v-card-title>
                                        <v-data-table
                                            :headers="headers"
                                            :items="imports"
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
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-print@0.3.0/dist/vueprint.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <script>
        new Vue({
            el: '#emp_wcl',
            vuetify: new Vuetify(),
            data () {
                return {
                    company_id: 0,
                    kpp_id: 0,
                    import_file: '',
                    imports: [],
                    companies: <?php echo json_encode($companies) ?>,
                    kpps: <?php echo json_encode($kpps) ?>,
                    headers: [
                        { text: 'Компания', value: 'company_name' },
                        { text: 'Ф.И.О', value: 'full_name' },
                        { text: 'Должность', value: 'p_name' },
                        { text: 'Гос.номер', value: 'gov_number' },
                        { text: 'Марка', value: 'mark_car' },
                        { text: 'Статус', value: 'status' },
                        { text: 'КПП', value: 'kpp_name' },
                        { text: 'Дата', value: 'date' },
                    ],
                }
            },
            methods: {
                executeImport() {
                    let formData = new FormData();
                    formData.append('company_id', this.company_id);
                    formData.append('kpp_id', this.kpp_id);
                    formData.append('import_file', this.import_file);
                    axios.post('/admin/white-car-list/import-execute', formData)
                        .then(res => {
                            console.log(res.data)
                            this.imports = res.data;
                        })
                        .catch(err => console.log(err))
                },
            }
        })
    </script>
@endpush
