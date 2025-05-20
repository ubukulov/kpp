<script>
import axios from "axios";
import dateformat from "dateformat";
export default {
    data() {
        return {
            auto_tabs: null,
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
                    'title': 'Все заявки (включая завершенные)',
                    'id': 4
                },
            ],
            headers: [
                {
                    text: '№ заявки',
                    align: 'start',
                    sortable: true,
                    value: 'id',
                },
                { text: 'Тип', value: 'task_type'},
                { text: 'Тип авто', value: 'trans_type'},
                { text: 'Статус', value: 'status' },
                { text: 'Файл', value: 'upload_file' },
                { text: 'История', value: 'import_logs' },
                { text: 'Ред.', value: 'edit' },
                { text: 'Дата', value: 'created_at' },
                { text: '', value: 'data-table-expand' }
            ],
            filter_id: 0,
            container_tasks: [],
            container_logs: [],
            isLoaded: true,
            expanded: [],
            singleExpand: false,
            search: '',
            container_number: null,
            system: {
                success: false,
                error: false,
            },
            info_container: '',
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
        getContainerLogs(){
            axios.get(`/container-terminals/container/${this.container_number}/get-logs/`)
                .then(res => {
                    console.log(res);
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
        convertDateToOurFormat(dt){
            return dateformat(dt, 'dd.mm.yyyy HH:MM');
        },
        returnValueFromArray(text){
            return this.operation_type.find(function(i){
                if (i.text === text) {
                    return i.value;
                }
            });
        },
    },
    created() {
        this.getContainerTasks();
    }
}
</script>

<template>
    <v-app>
        <v-container>

            <v-tabs
                v-model="auto_tabs"
                background-color="deep-orange-accent-1"
                dark
                icons-and-text
            >
                <v-tabs-slider></v-tabs-slider>
                <v-tab href="#orders">
                    Заявки
                    <v-icon>mdi-file-document-multiple</v-icon>
                </v-tab>

                <v-tab href="#history">
                    История
                    <v-icon>mdi-history</v-icon>
                </v-tab>

                <v-tabs-items v-model="auto_tabs">
                    <v-tab-item :value="'orders'">
                        <v-card flat>
                            <a style="margin: 10px; color: white;" class="btn btn-success" href="/container-terminals/create-task">Создать заявку</a>
                            <v-card-title>
                                Заявки
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
                                :single-expand="singleExpand"
                                :expanded.sync="expanded"
                                show-expand
                                class="elevation-1"
                                loading-text="Загружается... Пожалуйста подождите"
                            >
                                <template v-slot:item.id="{ item }">
                                    {{ (item.task_type === 'receive') ? 'IN_'+ item.id : 'OUT_' + item.id }}
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
                                    <div v-else-if="item.status === 'waiting'">
                                        В ожидание
                                    </div>
                                    <div v-else-if="item.status === 'ignore'">
                                        Аннулирован
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
                                    <div>
                                        <a :href="'/container-terminals/task/'+item.id+'/import-logs'"><i style="font-size: 20px;" class="fa fa-history"></i></a>
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

                                <template v-slot:expanded-item="{ headers, item }">
                                    <td :colspan="headers.length" style="padding: 10px 20px;">
                                        <p style="margin-bottom: 15px;"><strong>Информация по заявке: </strong> {{ (item.task_type === 'receive') ? 'IN_'+ item.id : 'OUT_' + item.id }}</p>
                                        <table class="table table-bordered">
                                            <thead>
                                            <th>Создал заявку</th>
                                            <th>Тип</th>
                                            <th>Выполнение</th>
                                            <th>Печать</th>
                                            </thead>

                                            <tbody>
                                            <tr>
                                                <td>
                                                    <span v-if="item.user">{{ item.user.full_name }}</span>
                                                    <span v-else></span>
                                                </td>
                                                <td>
                                                    <span v-if="item.kind === 'common'">Обычный</span>
                                                    <span v-else>Автоматический</span>
                                                </td>
                                                <td>{{ item.stat }}</td>
                                                <td>
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
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </template>
                            </v-data-table>
                        </v-card>
                    </v-tab-item>

                    <v-tab-item :value="'history'">
                        <v-card flat>
                            <v-app>
                                <v-container>
                                    <v-row class="mt-4">
                                        <v-col cols="3">
                                            <v-text-field
                                                label="Введите номер контейнера"
                                                v-model="container_number"
                                            ></v-text-field>

                                        </v-col>

                                        <v-col cols="2">
                                            <v-btn
                                                class="btn success"
                                                @click="getContainerLogs()"
                                            >
                                                <v-icon>mdi-book-search</v-icon>
                                                Найти
                                            </v-btn>
                                        </v-col>
                                    </v-row>

                                    <v-row class="mt-2">
                                        <v-col cols="12">
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
                                                                {{ returnValueFromArray(item.operation_type).value }}
                                                            </h2>
                                                            <div>
                                                                <div><strong>Пользователь:</strong> {{ item.user.full_name }}</div>
                                                                <div><strong>Телефон:</strong> {{ item.user.phone }}</div>
                                                                <div><strong>Клиент:</strong> {{ item.company }}</div>
                                                                <div><strong>Номер машины/вагона:</strong> {{ item.car_number_carriage }}</div>
                                                                <div><strong>Контейнер:</strong> {{ item.container_number }}</div>
                                                                <table class="table table-bordered mt-2">
                                                                    <thead>
                                                                    <th>Из</th>
                                                                    <th>В</th>
                                                                    <th>Состояние</th>
                                                                    </thead>

                                                                    <tbody>
                                                                    <tr>
                                                                        <td>
                                                                            <span>{{ item.address_from }}</span>
                                                                        </td>
                                                                        <td>
                                                                            <span>{{ item.address_to }}</span>
                                                                        </td>
                                                                        <td>{{ item.state }}</td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>

                                                            </div>
                                                        </div>
                                                    </v-timeline-item>
                                                </v-timeline>
                                            </template>

                                            <div v-if="system.error" style="font-size: 20px;
                                        line-height: 20px;
                                        margin-bottom: 30px;
                                        font-weight: bold;
                                        border: 1px solid yellowgreen;
                                        text-align: center;
                                        padding: 10px;" v-html="info_container">

                                            </div>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </v-app>
                        </v-card>
                    </v-tab-item>
                </v-tabs-items>
            </v-tabs>

        </v-container>
    </v-app>
</template>

<style scoped>

</style>
