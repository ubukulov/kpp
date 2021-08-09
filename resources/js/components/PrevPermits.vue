<template>
    <div class="row">
        <div class="col-md-12 right_content" style="background: burlywood; height: 100vh;">
            <div>
                <h3>Список предварительных пропусков</h3>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <button style="color: #fff;" class="btn btn-dark" type="button" @click="getPermits()">Обновить</button>
                    </div>
                </div>
            </div>
            <template>
                <v-data-table
                    :headers="headers"
                    :items="permits"
                    item-key="name"
                    class="elevation-1"
                    :search="search"
                >
                    <template v-slot:top>
                        <v-text-field
                            v-model="search"
                            label="Поиск"
                            class="mx-4"
                        ></v-text-field>
                    </template>
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
                            @click="getPermit(item.id)"
                        >
                            mdi-account-edit
                        </v-icon>
                    </template>

                </v-data-table>
            </template>
        </div>

        <template>
            <v-dialog style="z-index: 99999; position: relative;" v-model="dialog" persistent max-width="800px">
                <v-card>
                    <v-card-title>
                        <span class="headline" style="font-size: 40px !important;">УТОЧНИТЕ КОМПАНИЮ</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="6">
                                    <div class="form-group">
                                        <label>Уточните компанию у водителя</label>
                                        <v-autocomplete
                                            :items="companies"
                                            class="form-control"
                                            style="border: 3px solid blue;"
                                            :hint="`${companies.id}, ${companies.short_en_name}`"
                                            :search-input.sync="searchInput"
                                            item-value="id"
                                            v-model="company_id"
                                            item-text="short_en_name"
                                            autocomplete
                                        ></v-autocomplete>
                                    </div>
                                </v-col>

                                <v-col cols="6">
                                    <div class="form-group">
                                        <label>Иностранная машина?</label>
                                        <v-autocomplete
                                            :items="foreign_cars"
                                            class="form-control"
                                            style="border: 3px solid blue;"
                                            :hint="`${foreign_cars.id}, ${foreign_cars.text}`"
                                            item-value="f_id"
                                            v-model="foreign_car"
                                            item-text="text"
                                            autocomplete
                                        ></v-autocomplete>
                                    </div>
                                </v-col>

                                <v-col cols="6" class="prt_col">
                                    <div>
                                        <p>Вод. удос:</p> <span>{{ ud_number }}</span>
                                    </div>
                                    <div>
                                        <p>ФИО водителя:</p> <span>{{ last_name }}</span>
                                    </div>
                                    <div>
                                        <p>Номер водителя:</p> <span>{{ phone }}</span>
                                    </div>
                                    <div>
                                        <p>Вид операции:</p> <span>{{ operation_type }}</span>
                                    </div>
                                </v-col>

                                <v-col cols="6" class="prt_col">
                                    <div>
                                        <p>Тех.паспорт:</p> <span>{{ tex_number }}</span>
                                    </div>
                                    <div>
                                        <p>Гос.номер:</p> <span>{{ gov_number }}</span>
                                    </div>
                                    <div>
                                        <p>Марка авто:</p> <span>{{ mark_car }}</span>
                                    </div>
                                    <div>
                                        <p>Номер прицепа:</p> <span>{{ pr_number }}</span>
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
                        <v-btn color="blue darken-1" @click="closeForm()">Отменить</v-btn>
                        <v-btn color="success darken-1" @click="print_r(id)">
                            <v-icon
                                middle
                            >
                                mdi-printer
                            </v-icon>
                            &nbsp;Распечатать
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </template>
    </div>
</template>

<script>
    import axios from "axios";
    import dateformat from "dateformat";

    export default {
        data(){
            return {
                id: 0,
                permits: [],
                interval: 0,
                search: '',
                calories: '',
                computer_name: 1,
                dialog: false,
                mark_car: '',
                gov_number: '',
                pr_number: '',
                ud_number: '',
                tex_number: '',
                last_name: '',
                operation_type: '',
                phone: '',
                company: '',
                company_id: 0,
                foreign_car: 0,
                searchInput: '',
                errors: [],
                headers: [
                    {
                        text: 'ИД',
                        align: 'start',
                        sortable: true,
                        value: 'id',
                    },
                    {
                        text: 'ФИО',
                        align: 'start',
                        sortable: true,
                        value: 'last_name',
                    },
                    {
                        text: '#вод.удос.',
                        value: 'ud_number',
                    },
                    { text: 'Вид операции', value: 'operation_type' },
                    { text: 'Телефон', value: 'phone' },
                    { text: 'Гос.номер', value: 'gov_number' },
                    { text: 'Марка', value: 'mark_car' },
                    { text: 'Дата создания', value: 'created_at' },
                    { text: 'Ред.', value: 'edit' },
                ],
                foreign_cars: [
                    {
                        f_id: 0, text: 'Не указано'
                    },
                    {
                        f_id: 1, text: 'Казахстанская'
                    },
                    {
                        f_id: 2, text: 'Иностранная'
                    }
                ]
            }
        },
        props: [
            'companies'
        ],
        methods: {
            getPermits(){
                axios.get('/get-prev-permits-for-today')
                    .then(res => {
                        this.permits = res.data;
                    })
                    .catch(err => {
                        if(err.response.status == 401) {
                            window.location.href = '/login';
                        } else {
                            console.log(err)
                        }
                    })
            },
            print_r(id){
                this.errors = [];
                if (this.company_id == 0) {
                    this.errors.push('Укажите компанию');
                }
                if (this.foreign_car == 0) {
                    this.errors.push('Машина иностранная?');
                }

                if (this.errors.length == 0) {
                    axios.get('/command/print/'+id+"/"+this.company_id+"/"+this.foreign_car)
                        .then(res => {
                            console.log(res);
                            this.getPermits();
                            this.dialog = false;
                        })
                        .catch(err => {
                            if(err.response.status == 401) {
                                window.location.href = '/login';
                            } else {
                                console.log(err)
                            }
                        })
                }
            },
            convertDateToOurFormat(dt){
                return dateformat(dt, 'dd.mm.yyyy HH:MM');
            },
            getPermit(id){
                axios.get('/get-permit-by-id/'+id)
                .then(res => {
                    this.id = id;
                    this.ud_number = res.data.ud_number;
                    this.tex_number = res.data.tex_number;
                    this.mark_car = res.data.mark_car;
                    this.gov_number = res.data.gov_number;
                    this.pr_number = res.data.pr_number;
                    this.last_name = res.data.last_name;
                    if (res.data.operation_type == 1) {
                        this.operation_type = 'Погрузка';
                    } else if(res.data.operation_type == 2){
                        this.operation_type = 'Разгрузка';
                    } else {
                        this.operation_type = 'Другие действия';
                    }
                    this.phone = res.data.phone;
                    this.company = res.data.company;
                    this.dialog = true;
                })
                .catch(err => {
                    if(err.response.status == 401) {
                        window.location.href = '/login';
                    } else {
                        console.log(err)
                    }
                })
            },
            closeForm(){
                this.getPermits();
                this.errors = [];
                this.dialog = false;
            }
        },
        created(){
            this.getPermits();
            //this.interval = setInterval(() => this.getPermits(), 30000);
        }
    }
</script>

<style scoped>

</style>
