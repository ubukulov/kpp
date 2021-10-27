<template>
    <v-app>
        <template>
            <v-card>
                <v-card-title>
                    Список заявков
                    <v-spacer></v-spacer>
                    <v-select
                        :items="filters"
                        :hint="`${filters.id}, ${filters.title}`"
                        item-value="id"
                        v-model="filter_id"
                        item-text="title"
                        @change="getContainerTasks()"
                    ></v-select>
                </v-card-title>

                <v-data-table
                    :headers="headers"
                    :items="container_tasks"
                    :items-per-page="20"
                    :search="search"
                    :loading="isLoaded"
                    loading-text="Загружается... Пожалуйста подождите"
                >
                    <template v-slot:item.id="{ item }">
                        {{ (item.task_type === 'receive') ? 'IN_'+ item.id : 'OUT_' + item.id }}
                    </template>

                    <template v-slot:item.user="{ item }">
                        {{ item.user.full_name }}
                    </template>

                    <template v-slot:item.task_type="{ item }">
                        {{ (item.task_type === 'receive') ? 'Прием' : 'Выдача' }}
                    </template>

                    <template v-slot:item.trans_type="{ item }">
                        {{ (item.trans_type === 'train') ? 'ЖД' : 'Авто' }}
                    </template>

                    <template v-slot:item.status="{ item }">
                        <div v-if="item.status === 'open'">
                            В работе <a :href="'/container-terminals/task/'+item.id+'/container-logs'">
                            <i :class="[{ allow: item.allow}]" style="font-size: 20px;" class="fa fa-history"></i></a>
                        </div>
                        <div v-else-if="item.status === 'failed'">
                            Ошибка при импорте
                        </div>
                        <div v-else>
                            Выполнен <a :href="'/container-terminals/task/'+item.id+'/container-logs'">
                            <i style="font-size: 20px;" class="fa fa-history"></i></a>
                        </div>
                    </template>

                    <template v-slot:item.upload_file="{ item }">
                        <div v-if="item.upload_file !== null">
                            <a :href="item.upload_file" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp;Скачать</a>
                        </div>
                    </template>

                    <template v-slot:item.import_logs="{ item }">
                        <div v-if="item.upload_file !== null">
                            <a :href="'/container-terminals/task/'+item.id+'/import-logs'"><i style="font-size: 20px;" class="fa fa-history"></i></a>
                        </div>
                    </template>

                    <template v-slot:item.print="{ item }">
                        <div v-if="item.status === 'open'">
                            <a :href="'/container-terminals/task/'+item.id+'/print'" target="_blank">
                                <v-icon
                                    title="Распечатать заявку"
                                    :color="(item.print_count === 0) ? '#000000' : '#006600'"
                                    middle
                                >
                                    mdi-printer
                                </v-icon>
                            </a>
                        </div>
                    </template>

                    <template v-slot:item.edit="{ item }">
                        <div v-if="item.status === 'failed'">
                            <a :href="'/container-terminals/task/'+item.id+'/edit'"><i style="font-size: 20px;" class="fa fa-edit"></i></a>
                        </div>
                    </template>

                    <template v-slot:item.created_at="{ item }">
                        {{ convertDateToOurFormat(item.created_at) }}
                    </template>
                </v-data-table>
            </v-card>
        </template>
    </v-app>
</template>

<script>
    import axios from 'axios'
    import dateformat from "dateformat";
    export default {
        props: [

        ],
        data(){
            return {
                container_tasks: [],
                search: '',
                headers: [
                    {
                        text: '№ заявка',
                        align: 'start',
                        sortable: false,
                        value: 'id',
                    },
                    {
                        text: 'Пользователь',
                        align: 'start',
                        sortable: true,
                        value: 'user',
                    },
                    { text: 'Тип', value: 'task_type'},
                    { text: 'Тип авто', value: 'trans_type'},
                    { text: 'Статус', value: 'status' },
                    { text: 'Файл', value: 'upload_file' },
                    { text: 'История', value: 'import_logs' },
                    { text: 'Печать', value: 'print' },
                    { text: 'Ред.', value: 'edit' },
                    { text: 'Дата', value: 'created_at' },
                ],
                isLoaded: true,
                filters: [
                    {
                        'title': 'Мои открытые заявки',
                        'id': 0
                    },
                    {
                        'title': 'Все открытые заявки:',
                        'id': 1
                    },
                    {
                        'title': 'Мои закрытые заявки',
                        'id': 2
                    },
                    {
                        'title': 'Мои ошибочные заявки',
                        'id': 3
                    },
                    {
                        'title': 'Все заявки (включая завершенные)',
                        'id': 4
                    },
                ],
                filter_id: 0,
                isAllow: false
            }
        },
        methods: {
            getContainerTasks(){
                this.container_tasks = [];
                this.isLoaded = true;

                axios.get('/container-terminals/get-container-tasks/' + this.filter_id)
                .then(res => {
                    console.log(res);
                    this.container_tasks = res.data.data;
                    this.isLoaded = false;
                })
                .catch(err => {
                    console.log(err);
                })
            },
            convertDateToOurFormat(dt){
                return dateformat(dt, 'dd.mm.yyyy HH:MM');
            },
            print_r(id){
                window.location.href = '/container-terminals/task/'+id+'/print'
            }
        },
        created(){
            this.getContainerTasks();
        }
    }
</script>

<style scoped>
    .allow {
        font-size: 20px; color: red; box-shadow: 0 0 0 0 rgba(0, 0, 0, 1);
        transform: scale(1); animation: pulse 2s infinite; border-radius: 50%;
    }
</style>
