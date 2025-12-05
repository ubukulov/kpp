@extends('cabinet.cabinet')
@push('cabinet_styles')
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">

    @include('cabinet.inc.vue-styles')

@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Поиск контейнера</h3>
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

                                    {{--<v-col v-if="success" cols="12">
                                        <template>
                                            <v-alert
                                                type="info"
                                                title="Результат поиска"
                                                v-html="info_html"
                                                variant="tonal"
                                            >

                                            </v-alert>
                                        </template>
                                    </v-col>--}}

                                    <template v-if="system.success">
                                        <v-timeline
                                        >
                                            <v-timeline-item
                                                v-for="(item, i) in container_logs"
                                                :key="i"
                                                color="orange"
                                                :right="true"
                                            >
                                                <template v-slot:opposite>
                                                    <span
                                                        :class="`headline font-weight-bold orange--text`"
                                                        v-text="item.transaction_date"
                                                    ></span>
                                                </template>

                                                <div class="py-4">
                                                    <h2 :class="`headline font-weight-light mb-4 orange--text`">
                                                        @{{ returnValueFromArray(item.operation_type).value }}
                                                    </h2>
                                                    <div>
                                                        <div><strong>Пользователь:</strong> @{{ item.user.full_name }}</div>
                                                        <div><strong>Телефон:</strong> @{{ item.user.phone }}</div>
                                                        <div><strong>Клиент:</strong> @{{ item.company }}</div>
                                                        <div><strong>Номер машины/вагона:</strong> @{{ item.car_number_carriage }}</div>
                                                        <div><strong>Контейнер:</strong> @{{ item.container_number }}</div>
                                                        <div><strong>Тип контейнера:</strong> @{{ item.container_type }}</div>
                                                        <table class="table table-bordered mt-2">
                                                            <thead>
                                                            <th>Из</th>
                                                            <th>В</th>
                                                            <th>Состояние</th>
                                                            </thead>

                                                            <tbody>
                                                            <tr>
                                                                <td>
                                                                    <span>@{{ item.address_from }}</span>
                                                                </td>
                                                                <td>
                                                                    <span>@{{ item.address_to }}</span>
                                                                </td>
                                                                <td>@{{ item.state }}</td>
                                                            </tr>
                                                            </tbody>
                                                        </table>

                                                    </div>
                                                </div>
                                            </v-timeline-item>
                                        </v-timeline>
                                    </template>
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

    @include('cabinet.inc.vue-scripts')

    <script>
        new Vue({
            el: '#cabinet_whc',
            vuetify: new Vuetify(),
            data(){
                return {
                    container_number: '',
                    success: false,
                    info_html: '',
                    system: {
                        success: false,
                        error: false,
                    },
                    container_logs: [],
                    operation_type: [
                        { text: 'incoming', value: 'Заявка на размещение' },
                        { text: 'received', value: 'Размещен' },
                        { text: 'in_order', value: 'Заявка на выдачу' },
                        { text: 'shipped', value: 'Отобран' },
                        { text: 'completed', value: 'Выдан' },
                        { text: 'canceled', value: 'Отменен' },
                        { text: 'edit', value: 'Запрос на изменение' },
                        { text: 'edit_completed', value: 'Изменен' },
                    ],
                }
            },
            methods: {
                findContainer(){
                    let formData = new FormData();
                    formData.append('container_number', this.container_number);

                    axios.post('/cabinet/webcont/aftos/search', formData)
                        .then(res => {
                            this.container_logs = res.data;
                            this.system.success = true;
                            this.system.error = false;
                        })
                        .catch(err => {
                            if(err.response.status === 404) {
                                this.system.success = false;
                                this.system.error = true;
                                this.info_container = err.response.data;
                            }
                            console.log(err.response)
                        })
                },
                returnValueFromArray(text){
                    return this.operation_type.find(function(i){
                        if (i.text === text) {
                            return i.value;
                        }
                    });
                },
            }
        })
    </script>
@endpush
