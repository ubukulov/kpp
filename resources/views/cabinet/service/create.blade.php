@extends('cabinet.cabinet')
@push('cabinet_styles')
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">

    @include('cabinet.inc.vue-styles')

@endpush
@section('content')
    <div class="row" id="catalogOrder">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Заявки в техслужбу</h3>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <v-app>
                        <v-main>
                            <v-container>
                                <v-row>
                                    <v-col md="6">
                                        <v-autocomplete
                                            :items="rootServices"
                                            v-model="root_service_id"
                                            item-value="id"
                                            item-text="name"
                                            label="Выберите категорию"
                                            required
                                        ></v-autocomplete>
                                    </v-col>

                                    <v-col md="6">
                                        <v-autocomplete
                                            :items="childServices"
                                            v-model="service_id"
                                            item-value="id"
                                            item-text="name"
                                            label="Выберите Тип работ"
                                            required
                                            @change="getServiceDescription()"
                                        ></v-autocomplete>
                                    </v-col>

                                    {{--<v-col cols="12">
                                        <label>Тема</label>
                                        <v-text-field
                                            type="text"
                                            v-model="name"
                                            required
                                            solo
                                        ></v-text-field>
                                    </v-col>--}}

                                    <v-col cols="12">
                                        <label>Описание заявки</label>
                                        {{--<v-textarea
                                            clear-icon="mdi-close-circle"
                                            model-value="This is clearable text."
                                            clearable
                                            solo
                                            v-html="description"
                                            auto-grow
                                            variant="outlined"
                                        ></v-textarea>--}}
                                        <vue-editor v-model="description" :editor-toolbar="customToolbar"></vue-editor>
                                    </v-col>

                                    <v-col cols="12">
                                        <v-btn
                                            color="blue-darken"
                                            outlined
                                            @click="create()"
                                        >Создать заявку</v-btn>
                                    </v-col>
                                </v-row>
                            </v-container>

                            <!-- Оверлей загрузки -->
                            <v-overlay :value="loading" absolute opacity="0.7">
                                <v-progress-circular
                                    indeterminate
                                    size="64"
                                    color="blue"
                                ></v-progress-circular>
                            </v-overlay>
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
    <script src="{{ asset('js/quill.min.js') }}"></script>
    <link href="{{ asset('css/quill.snow.css') }}" rel="stylesheet">
    <script src="{{ asset('js/vue2-editor.umd.min.js') }}"></script>

    <script>
        const { VueEditor } = window.Vue2Editor;

        new Vue({
            el: '#catalogOrder',
            vuetify: new Vuetify(),
            components: { 'vue-editor': window.Vue2Editor.VueEditor },
            data() {
                return {
                    services: <?php echo json_encode($services) ?>,
                    root_service_id: null,
                    service_id: null,
                    rootServices: [],
                    name: null,
                    description: null,
                    loading: false,
                    customToolbar: [
                        ['bold', 'italic', 'underline'], // базовые стили
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link'], // оставляем только ссылки
                        ['clean'] // убрать форматирование
                    ]
                }
            },
            methods: {
                create(){
                    this.loading = true;
                    let formData = new FormData();
                    formData.append('root_service_id', this.root_service_id);
                    formData.append('service_id', this.service_id);
                    formData.append('description', this.description);

                    axios.post('/cabinet/services/store', formData)
                        .then(res => {
                            this.loading = false;
                            window.location.href = '/cabinet/services/';
                        })
                        .catch(err => {
                            this.loading = false;
                            console.log(err)
                        })
                },
                getServiceDescription(){
                    const service = this.services.find(s => s.id === this.service_id);
                    console.log(service);
                    this.description = service ? service.description : '';
                }
            },
            computed: {
                childServices() {
                    return this.services.filter(s => s.parent_id === this.root_service_id);
                }
            },
            created(){
                this.rootServices = this.services.filter(s => s.parent_id === 0);
            }
        });
    </script>
@endpush
