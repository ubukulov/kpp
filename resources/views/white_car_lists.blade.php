@extends('layouts.wcl')
@push('styles')
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <style>
        .dataTables_filter input {
            margin-left: 0 !important;
            display: block !important;
            width: 100% !important;
        }
    </style>
@endpush
@section('content')
    <div id="wcl3_kkk">
        <v-app>
            <v-main>
                <v-container>
                    <v-row class="text-center mt-5">
                        <v-col cols="12">
                            <h3 class="card-title">Список постоянных машин</h3>
                        </v-col>

                        <v-col cols="6" class="text-right">
                            <v-text-field
                                style="width: 300px; float: right;"
                                label="Введите номер машины"
                                v-model="search_key"
                                prepend-inner-icon="mdi-car"
                                :rules="[() => !!search_key || 'This field is required']"
                                solo
                            ></v-text-field>
                        </v-col>

                        <v-col cols="6">
                            <v-btn
                                color="success"
                                style="padding: 24px; float: left;"
                                @click="searchByNumber"
                            >
                                <v-icon left size="25">
                                    mdi-search-web
                                </v-icon>
                                Поиск
                            </v-btn>
                        </v-col>
                    </v-row>

                    <template v-if="search_arr.length <= 5 && search_arr.length != 0">
                        <h4>Список найденных машин</h4>
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Гос.номер</th>
                                <th>Клиент</th>
                                <th>КПП</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item,i) in search_arr" v-bind:key="i">
                                    <td>
                                        <v-btn type="button" @click="fixDateInTime(item)"  class="primary">@{{ item.gov_number }}</v-btn>
                                    </td>
                                    <td>@{{ item.short_ru_name }}</td>
                                    <td v-if="item.permit">
                                        <span style="color: green;"><strong>@{{ item.kpp_name.toUpperCase() }}</strong></span>
                                    </td>
                                    <td v-if="!item.permit">
                                        <span style="color: red;"><strong>@{{ item.kpp_name.toUpperCase() }}</strong></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </template>

                    <template v-if="search_arr.length > 5">
                        <v-col cols="12" class="text-center">
                            <v-alert
                                border="bottom"
                                colored-border
                                type="warning"
                                elevation="2"
                                style="width: 700px; margin: auto;"
                            >
                                Найден более 5-х записей. Для точного результата укажите еще больше символей.
                            </v-alert>
                        </v-col>
                    </template>

                    <template v-if="failureRes">
                        <v-col cols="12" class="text-center">
                            <v-alert
                                border="bottom"
                                colored-border
                                type="error"
                                elevation="2"
                                style="width: 700px; margin: auto;"
                            >
                                По вашему запросу ничего не найдено.
                            </v-alert>
                        </v-col>
                    </template>

                    <template>
                        <v-dialog style="z-index: 99999; position: relative;" v-model="dialog" persistent max-width="400px">
                            <v-card>
                                <v-card-title>

                                </v-card-title>
                                <v-card-text>
                                    <v-container>
                                        <v-row>
                                            <v-col cols="12" class="text-center">
                                                <v-icon style="font-size: 60px; color: green; margin-bottom: 20px;">
                                                    mdi-check-circle
                                                </v-icon>
                                                <p v-html="successTxt" style="font-size: 20px; color: green;">

                                                </p>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </v-card-text>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn color="blue darken-1" @click="closeDialog">Закрыть</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-dialog>
                    </template>
                </v-container>
            </v-main>
        </v-app>
    </div>
@stop

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-print@0.3.0/dist/vueprint.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <!-- DataTables -->
    <script src="/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

    <script>
        const wcl_kkk = new Vue({
            el: '#wcl3_kkk',
            vuetify: new Vuetify(),
            data () {
                return {
                    dialog: false,
                    successTxt: "",
                    search_key: '',
                    search_arr: [],
                    failureRes: false,
                }
            },
            methods: {
                fixDateInTime(item) {
                    let formData = new FormData();
                    formData.append('wcl_com_id', item.wcl_com_id);
                    axios.post(`/white-car-lists/${item.wcl_com_id}/fix-date-in-time`, formData)
                        .then(res => {
                            console.log(res)
                            this.successTxt = `Номер машины: ${res.data.gov_number}. <br>Заезд зафиксирован!`
                            this.dialog = true;
                        })
                        .catch(err => {
                            console.log(err)
                        })
                },
                searchByNumber(){
                    this.failureRes = false;
                    if(this.search_key !== '') {
                        let formData = new FormData();
                        formData.append('search_key', this.search_key);
                        axios.post(`/white-car-lists/search-by-number`, formData)
                            .then(res => {
                                console.log(res)
                                this.search_arr = res.data;
                                if(this.search_arr.length === 0) {
                                    this.failureRes = true;
                                }
                            })
                            .catch(err => {
                                console.log(err)
                            })
                    }

                },
                closeDialog(){
                    this.dialog = false;
                    this.search_key = '';
                    this.search_arr = [];
                }
            }
        })
    </script>
@endpush
