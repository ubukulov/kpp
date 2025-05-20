<template>
    <div class="row" style="background-color: thistle; padding: 20px 0;">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <button style="color: #fff;" class="btn btn-dark" type="button" @click="getNotCompletedPermitsList()">Обновить</button>
                    </div>
                </div>
            </div>

            <template>
                <v-card>
                    <v-card-title>
                        Список пропусков на территории
                        <v-spacer></v-spacer>
                        <v-text-field
                            v-model="search"
                            append-icon="mdi-magnify"
                            label="Search"
                            single-line
                            hide-details
                        ></v-text-field>
                    </v-card-title>

                    <v-data-table
                        :headers="headers"
                        :items="listNotCompletedPermits"
                        :items-per-page="20"
                        :search="search"
                        :loading="isLoaded"
                        loading-text="Загружается... Пожалуйста подождите"
                    >
                        <template v-slot:item.operation_type="{ item }">
                            <span v-if="item.operation_type == 1">Погрузка</span>
                            <span v-else-if="item.operation_type == 2">Разгрузка</span>
                            <span v-else>Другие действия</span>
                        </template>
                        <template v-slot:item.created_at="{ item }">
                            {{convertDateToOurFormat(item.created_at)}}
                        </template>

                        <template v-slot:item.edit="{ item }">
                            <v-icon
                                middle
                                @click="print_r(item.id)"
                            >
                                mdi-printer
                            </v-icon>
                            &nbsp;&nbsp;
                            <v-icon
                                title="Перенести в архив"
                                middle
                                @click="showArchiveForm(item.id)"
                            >
                                mdi-archive
                            </v-icon>
                        </template>
                    </v-data-table>
                </v-card>
            </template>

            <template>
                <v-dialog style="z-index: 99999; position: relative;" v-model="dialog" persistent max-width="800px">
                    <v-card>
                        <v-card-title>
                            <span class="headline" style="font-size: 40px !important;">Перенести в архив пропуск №{{ permit_id }}</span>
                        </v-card-title>
                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12">
                                        <div class="form-group">
                                            <label>Укажите причину</label>
                                            <v-textarea
                                                background-color="amber lighten-4"
                                                color="orange orange-darken-4"
                                                v-model="note"
                                            ></v-textarea>
                                        </div>
                                    </v-col>

                                    <v-col cols="12">
                                        <div class="form-group">
                                            <p v-if="errors.length" style="margin-bottom: 0px !important;">
                                                <b>Пожалуйста исправьте указанные ошибки:</b>
                                            <ul style="color: red; padding-left: 15px; list-style: circle; text-align: left;">
                                                <li v-for="error in errors">{{error}}</li>
                                            </ul>
                                            </p>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="blue darken-1" @click="closeArchiveForm()">Отменить</v-btn>
                            <v-btn color="success darken-1" @click="putArchive()">
                                <v-icon
                                    middle
                                >
                                    mdi-archive
                                </v-icon>
                                &nbsp;Перенести в архив
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </template>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import dateformat from "dateformat";
    export default {
        data() {
            return {
                search: '',
                headers: [
                    {
                        text: 'ИД',
                        align: 'start',
                        sortable: false,
                        value: 'id',
                    },
                    {
                        text: 'ФИО',
                        align: 'start',
                        sortable: true,
                        value: 'last_name',
                    },
                    { text: '#Тех.паспорт', value: 'tex_number'},
                    { text: '#Вод.удос.', value: 'ud_number'},
                    { text: 'Вид операции', value: 'operation_type' },
                    { text: 'Телефон', value: 'phone' },
                    { text: 'Гос.номер', value: 'gov_number' },
                    { text: 'Марка', value: 'mark_car' },
                    { text: 'Дата заезда', value: 'date_in' },
                    { text: 'Дата выезда', value: 'date_out' },
                    { text: 'Вх.контейнер', value: 'incoming_container_number' },
                    { text: 'Исх.контейнер', value: 'outgoing_container_number' },
                    { text: 'Печать', value: 'edit' },
                ],
                listNotCompletedPermits: [],
                isLoaded: true,
                dialog: false,
                note: '',
                errors: [],
                permit_id: 0,
            }
        },
        methods: {
            getNotCompletedPermitsList(){
                this.isLoaded = true;
                axios.get('/get-not-completed-permits-for-week')
                .then(res => {
                    console.log(res.data);
                    this.listNotCompletedPermits = res.data;
                    this.isLoaded = false;
                })
            },
            convertDateToOurFormat(dt){
                return dateformat(dt, 'dd.mm.yyyy HH:MM');
            },
            print_r(id){
                axios.get('/command/print/'+id)
                    .then(res => {
                        console.log(res)
                    })
                    .catch(err => {
                        if(err.response.status == 401) {
                            window.location.href = '/login';
                        } else {
                            console.log(err)
                        }
                    })
            },
            showArchiveForm(id){
                this.errors = [];
                this.permit_id = id;
                this.dialog = true;
            },
            putArchive(){
                this.errors = [];
                if (!this.note) {
                    this.errors.push('Укажите причину');
                }
                if (this.errors.length === 0) {
                    let formData = new FormData();
                    formData.append('permit_id', this.permit_id);
                    formData.append('note', this.note);
                    axios.post(`/permit/${this.permit_id}/put-to-archive`, formData)
                        .then(res => {
                            console.log(res);
                            this.dialog = false;
                            this.listNotCompletedPermits = this.getNotCompletedPermitsList();
                            this.permit_id = 0;
                            this.note = '';
                            this.errors = [];
                        })
                        .catch(err => {
                            if (err.response.status === 403) {
                                this.errors.push(err.response.data);
                            }
                        })
                }
            },
            closeArchiveForm(){
                this.dialog = false;
                this.note = '';
                this.errors = [];
            },
        },
        created(){
            this.getNotCompletedPermitsList();
        }
    }
</script>

<style scoped>

</style>
