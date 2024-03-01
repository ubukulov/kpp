@extends('cabinet.cabinet')
@push('cabinet_styles')
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
                        <div class="col-6">
                            <h3 class="card-title">Список заявок</h3>
                        </div>

                        <div class="col-6 text-right">

                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div id="cabinet_whc" class="card-body">
                    <v-app>
                        <v-main>
                            <v-container>
                                <v-row>
                                    <v-col cols="6">
                                        <v-text-field v-model="container_number" label="Номер контейнера"></v-text-field>
                                    </v-col>

                                    <v-col cols="6">
                                        <v-btn
                                            color="success"
                                            class="mt-4"
                                            block
                                            @click="findContainer()"
                                        >
                                            <v-icon>
                                                mdi-database-search
                                            </v-icon>
                                            Поиск
                                        </v-btn>
                                    </v-col>

                                    <v-col v-if="success" cols="12">
                                        <template>
                                            <v-alert
                                                type="info"
                                                title="Результат поиска"
                                                v-html="info_html"
                                                variant="tonal"
                                            >

                                            </v-alert>
                                        </template>
                                    </v-col>
                                </v-row>
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

@push('cabinet_scripts')
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-print@0.3.0/dist/vueprint.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <script>
        new Vue({
            el: '#cabinet_whc',
            vuetify: new Vuetify(),
            data(){
                return {
                    container_number: '',
                    success: false,
                    info_html: ''
                }
            },
            methods: {
                findContainer(){
                    let formData = new FormData();
                    formData.append('container_number', this.container_number);

                    axios.post('/cabinet/webcont/aftos/search', formData)
                        .then(res => {
                            this.info_html = res.data.text;
                            this.success = true;
                        })
                        .catch(err => {
                            if(err.response.status === 404 || err.response.status === 403) {
                                this.info_html = err.response.data.text;
                                this.success = true;
                            }
                        })
                },
            }
        })
    </script>
@endpush
