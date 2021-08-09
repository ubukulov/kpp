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
                        </template>
                    </v-data-table>
                </v-card>
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
                    {
                        text: '#вод.удос.',
                        value: 'ud_number',
                    },
                    { text: 'Вид операции', value: 'operation_type' },
                    { text: 'Телефон', value: 'phone' },
                    { text: 'Гос.номер', value: 'gov_number' },
                    { text: 'Марка', value: 'mark_car' },
                    { text: 'Дата заезда', value: 'date_in' },
                    { text: 'КПП', value: 'kpp_name' },
                    { text: 'Печать', value: 'edit' },
                ],
                listNotCompletedPermits: [],
                isLoaded: true,
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
        },
        created(){
            this.getNotCompletedPermitsList();
        }
    }
</script>

<style scoped>

</style>
