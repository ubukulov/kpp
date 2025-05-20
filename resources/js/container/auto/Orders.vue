<script>
import dateformat from "dateformat";
import { mapActions } from 'vuex';
import axios from "axios";
import SpineModal from "../../components/modals/SpineModal.vue";
export default {
    components: {
        SpineModal
    },
    data() {
        return {
            technique_headers: [
                {
                    text: '№ заявки',
                    align: 'start',
                    sortable: true,
                    value: 'id',
                },
                { text: 'Клиент', value: 'short_en_name'},
                { text: 'Тип', value: 'task_type'},
                { text: 'Тип авто', value: 'trans_type'},
                { text: 'Статус', value: 'status' },
                { text: 'Файл', value: 'upload_file' },
                { text: 'Ред.', value: 'edit' },
                { text: 'Печать', value: 'print' },
                { text: 'Дата', value: 'created_at' },
            ],
            technique_tasks: [],
            search: '',
            isLoaded: true,
        }
    },
    methods: {
        ...mapActions(['showModal']),
        convertDateToOurFormat(dt){
            return dateformat(dt, 'dd.mm.yyyy HH:MM');
        },
        getTechniqueTasks(){
            this.technique_tasks = [];
            this.isLoaded = true;

            axios.get('/container-terminals/get-technique-tasks/')
                .then(res => {
                    console.log(res);
                    this.technique_tasks = res.data;
                    this.isLoaded = false;
                })
                .catch(err => {
                    console.log(err);
                })
        },
    },
    created() {
        this.getTechniqueTasks();
    }
}
</script>

<template>
    <v-card flat>
        <a style="margin: 10px; color: white;" class="btn btn-success" href="/container-terminals/technique-task-create">Создать заявку</a>
        <v-card-title>
            Заявки
            <v-spacer></v-spacer>
            <v-spacer></v-spacer>
            <v-btn @click="showModal">
                Корешок
            </v-btn>
        </v-card-title>

        <v-data-table
            :headers="technique_headers"
            :items="technique_tasks"
            :items-per-page="20"
            :search="search"
            :loading="isLoaded"
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
                    В работе <a :href="'/container-terminals/technique_task/'+item.id+'/show-details'">
                    <i :class="[{ allow: item.allow}]" style="font-size: 20px;" class="fa fa-history"></i></a>
                </div>
                <div v-else-if="item.status === 'failed'">
                    Ошибка при импорте
                </div>
                <div v-else-if="item.status === 'waiting'">
                    В ожидание
                </div>
                <div v-else>
                    Выполнен <a :href="'/container-terminals/technique_task/'+item.id+'/show-details'">
                    <i style="font-size: 20px;" class="fa fa-history"></i></a>
                </div>
            </template>

            <template v-slot:item.upload_file="{ item }">
                <div v-if="item.upload_file !== null">
                    <a :href="item.upload_file" target="_blank"><i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp;Скачать</a>
                </div>
            </template>

            <template v-slot:item.edit="{ item }">
                <div v-if="item.status === 'failed'">
                    <a :href="'/container-terminals/task/'+item.id+'/edit'"><i style="font-size: 20px;" class="fa fa-edit"></i></a>
                </div>
            </template>

            <template v-slot:item.print="{ item }">
                <div v-if="item.status === 'open'">
                    <a :href="'/container-terminals/technique-task/'+item.id+'/print'" target="_blank">
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

            <template v-slot:item.created_at="{ item }">
                {{ convertDateToOurFormat(item.created_at) }}
            </template>
        </v-data-table>

        <SpineModal></SpineModal>
    </v-card>
</template>

<style scoped>

</style>
