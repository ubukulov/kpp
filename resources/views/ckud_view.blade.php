<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/driver.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">

    <style>
        input, button{
            font-size: 14px !important;
        }
        label{
            font-size: 14px !important;
        }
    </style>
    <title>Детальная информация по CKUD</title>
</head>
<body>
<div class="container-fluid" id="app">
    <v-app>
        <v-main>
            <v-container>
                <div class="row">
                    {{--<div class="col-md-4">
                        <p><strong>Кол-во пропусков за текущий месяц: </strong>{{ $cur_month[0]->cnt }}</p>
                        <p><strong>Кол-во пропусков за предыдущий месяц: </strong>{{ $pre_month[0]->cnt }}</p>
                    </div>--}}

                    <div class="col-md-12">
                        <h4 class="text-center">Отчеты по СКУД</h4>
                    </div>

                    <div class="col-md-12">
                        <v-row>
                            <v-col md="2">
                                <div class="form-group">
                                    <v-autocomplete
                                        label="Выберите тип отчета"
                                        :items="reports"
                                        :hint="`${reports.id}, ${reports.text}`"
                                        item-value="id"
                                        v-model="report_id"
                                        item-text="text"
                                        autocomplete
                                    ></v-autocomplete>
                                </div>
                            </v-col>

                            <v-col md="2">
                                <v-menu
                                    class="menu3"
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
                                            label="Укажите дату"
                                            prepend-icon="event"
                                            readonly
                                            v-bind="attrs"
                                            v-on="on"
                                        ></v-text-field>
                                    </template>
                                    <v-date-picker v-model="to_date" :first-day-of-week="1" locale="ru" @input="menu3 = false"></v-date-picker>
                                </v-menu>
                            </v-col>

                            <v-col md="2">
                                <v-btn @click="downloadCKUDLogs()" type="button" class="success">Скачать</v-btn>
                            </v-col>

                            <v-col md="12">
                                <div v-if="progress_circule" class="text-center">
                                    <v-progress-circular
                                        style="width: 100px; height: 100px;"
                                        indeterminate
                                        color="primary"
                                    ></v-progress-circular>
                                </div>
                            </v-col>
                        </v-row>
                    </div>
                </div>
            </v-container>
        </v-main>
    </v-app>
</div>
<!-- DataTables -->
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
<script>
    new Vue({
        el: '#app',
        vuetify: new Vuetify(),
        data () {
            return {
                from_date: '',
                to_date: '',
                menu2: false,
                menu3: false,
                progress_circule: false,
                reports: [
                    {id: 0, text: 'Учет-анализ по КПП'},
                    {id: 1, text: 'Учет-анализ по компаниям'},
                ],
                report_id: 0,
            }
        },
        methods: {
            downloadCKUDLogs(){
                this.progress_circule = true;
                let formData = new FormData();
                formData.append('report_id', this.report_id);
                formData.append('to_date', this.to_date);
                axios.post('/view-detail-info-ckud-logs', formData)
                    .then(res => {
                        window.location.href = res.data;
                        this.progress_circule = false;
                    })
                    .catch(err => {
                        console.log(err);
                        this.progress_circule = false;
                    })
            }
        }
    });
</script>
</body>
</html>
