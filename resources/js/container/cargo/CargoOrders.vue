<script>
import dateformat from "dateformat";
import axios from "axios";
import {mapActions} from "vuex";
import CargoCreateModal from "../../components/modals/CargoCreateModal.vue";
export default {
    components: {
        CargoCreateModal
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
                { text: 'Тип', value: 'type'},
                { text: 'Статус', value: 'status' },
                { text: 'Ред.', value: 'edit' },
                { text: 'Дата', value: 'created_at' },
            ],
            cargo_tasks: [],
            search: '',
            isLoaded: true,
        }
    },
    methods: {
        ...mapActions(['showCargoModal']),
        convertDateToOurFormat(dt){
            return dateformat(dt, 'dd.mm.yyyy HH:MM');
        },
        getCargoTasks(){
            this.cargo_tasks = [];
            this.isLoaded = true;

            axios.get('/container-terminals/cargo/lists')
                .then(res => {
                    console.log(res);
                    this.cargo_tasks = res.data;
                    this.isLoaded = false;
                })
                .catch(err => {
                    console.log(err);
                })
        },
    },
    created() {
        this.getCargoTasks();
    }
}
</script>

<template>
    <v-card flat>
        <button style="margin: 10px; color: white;" class="btn btn-success" @click="showCargoModal">Создать заявку на груз</button>

        <CargoCreateModal></CargoCreateModal>

        <v-data-table
            :headers="technique_headers"
            :items="cargo_tasks"
            :items-per-page="20"
            :search="search"
            :loading="isLoaded"
            class="elevation-1"
            loading-text="Загружается... Пожалуйста подождите"
        >
            <template v-slot:item.id="{ item }">
                {{ (item.type === 'receive') ? 'IN_'+ item.id : 'OUT_' + item.id }}
            </template>

            <template v-slot:item.type="{ item }">
                {{ (item.type === 'receive') ? 'Прием' : 'Выдача' }}
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
    </v-card>
</template>

<style scoped>

</style>
