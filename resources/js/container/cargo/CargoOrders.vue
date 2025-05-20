<script>
import dateformat from "dateformat";
import axios from "axios";
import {mapActions} from "vuex";
import CargoCreateModal from "../../components/modals/CargoCreateModal.vue";
import CargoTaskCompleteModal from "../../components/modals/CargoTaskCompleteModal.vue";
import CargoPositionModal from "../../components/modals/CargoPositionModal.vue";
export default {
    components: {
        CargoPositionModal,
        CargoCreateModal,
        CargoTaskCompleteModal
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
                { text: 'Краткий н/м', value: 'short_number' },
                { text: 'Дата', value: 'created_at' },
                { text: 'Действие', value: 'action' },
                { text: '', value: 'data-table-expand' }
            ],
            cargo_tasks: [],
            search: '',
            isLoaded: true,
            dialog: false,
            dialogEdit: false,
            cargoTask: null,
            expanded: [],
            singleExpand: false,
            statuses: [
                { 'incoming': 'Не размещен' },
                { 'in_order': 'Не выдан' },
                { 'shipped': 'Выдан' },
                { 'received': 'Размещен' }
            ],
        }
    },
    methods: {
        ...mapActions(['showCargoModal', 'showCargoEditModal']),
        convertDateToOurFormat(dt){
            return dateformat(dt, 'dd.mm.yyyy HH:MM');
        },
        statusLabel(status){
            const found = this.statuses.find(s => Object.keys(s)[0] === status)
            return found ? Object.values(found)[0] : 'Неизвестный статус'
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
        getRowClass(item) {
            if (item.isClose) return 'closed';
            return '';
        },
        completedCargoTask(item){
            this.cargoTask = item;
            this.dialog = true;
        },
        editCargoTask(item){
            this.cargoTask = item;
            this.dialogEdit = true;
            console.log(item)
        }
    },
    created() {
        this.getCargoTasks();
    }
}
</script>

<template>
    <v-card flat>
        <button style="margin: 10px; font-size: 14px !important;" class="btn btn-outline-success" @click="showCargoModal">Создать заявку на груз</button>

        <CargoCreateModal @call-parent-method="getCargoTasks"></CargoCreateModal>

        <CargoPositionModal v-if="dialogEdit" :dialog="dialogEdit" :cargo-task="cargoTask" @update:dialog="dialogEdit = $event"></CargoPositionModal>

        <CargoTaskCompleteModal v-if="dialog" :dialog="dialog" :cargo-task="cargoTask" @update:dialog="dialog = $event"></CargoTaskCompleteModal>

        <v-data-table
            :headers="technique_headers"
            :items="cargo_tasks"
            :items-per-page="20"
            :search="search"
            :loading="isLoaded"
            :single-expand="singleExpand"
            :expanded.sync="expanded"
            show-expand
            class="elevation-1"
            loading-text="Загружается... Пожалуйста подождите"
            :item-class="getRowClass"
        >
            <template v-slot:item.id="{ item }">
                {{ (item.type === 'receive') ? 'IN_'+ item.id : 'OUT_' + item.id }}
            </template>

            <template v-slot:item.type="{ item }">
                {{ (item.type === 'receive') ? 'Прием' : 'Выдача' }}
            </template>

            <template v-slot:item.status="{ item }">
                <div v-if="item.status === 'new'">
                    Новый
                </div>
                <div v-else-if="item.status === 'processing'">
                    В работе
                </div>
                <div v-else-if="item.status === 'completed'">
                    Завершен
                </div>
                <div v-else-if="item.status === 'canceled'">
                    Отменен
                </div>
                <div v-else-if="item.status === 'failed'">
                    Ошибка
                </div>
                <div v-else>
                    Игнарирован
                </div>
            </template>

            <template v-slot:item.created_at="{ item }">
                {{ convertDateToOurFormat(item.created_at) }}
            </template>

            <template v-slot:item.action="{ item }">
                <div v-if="item.isClose">
                    <button @click="completedCargoTask(item)" style="font-size: 12px !important;" class="btn btn-success">Закрыть заявку</button>
                </div>

                <div v-if="item.status !== 'completed' && !item.isClose">
                    <button @click="editCargoTask(item)" style="font-size: 12px !important;" class="btn btn-outline-primary">Редактировать</button>
                </div>
            </template>

            <template v-slot:expanded-item="{ item }">
                <td :colspan="8" style="padding: 10px 20px;">
                    <div class="flex-space">
                        <div>
                            <p style="margin-bottom: 15px;"><strong>Информация по заявке: </strong> {{ (item.type === 'receive') ? 'IN_'+ item.id : 'OUT_' + item.id }}</p>
                            <p><strong>Оформил:</strong> {{ item.user.full_name }}</p>
                        </div>

                        <div>

                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr v-if="item.status !== 'completed'">
                                <th>№</th>
                                <th>Наименование</th>
                                <th>VINCODE</th>
                                <th>Кол-во</th>
                                <th>Вес(кг)</th>
                                <th>Машина/Вагон</th>
                                <th>Картинка</th>
                                <th>Статус</th>
                            </tr>

                            <tr v-if="item.status === 'completed'">
                                <th>№</th>
                                <th>VINCODE</th>
                                <th>Картинка</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="item.status !== 'completed'" v-for="(stock, index) in item.stocks" :key="index">
                                <td>{{ index+1 }}</td>
                                <td>{{ stock.cargo_information }}</td>
                                <td>{{ stock.vin_code }}</td>
                                <td>
                                    <span v-if="item.type === 'receive'">{{ stock.quantity }}</span>
                                    <span v-else>{{ stock.quantity_reserved }}</span>
                                </td>
                                <td>{{ stock.weight }}</td>
                                <td>{{ stock.car_number }}</td>
                                <td><a :href="stock.image" target="_blank"><img style="max-width: 100px;" :src="stock.image" alt=""></a></td>
                                <td>{{ statusLabel(stock.status) }}</td>
                            </tr>

                            <tr v-if="item.status === 'completed'" v-for="(stock, index) in item.history" :key="index">
                                <td>{{ index+1 }}</td>
                                <td>{{ stock.vin_code }}</td>
                                <td><a :href="stock.image" target="_blank"><img style="max-width: 100px;" :src="stock.image" alt=""></a></td>
                                <td>{{ statusLabel(stock.status) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </template>

        </v-data-table>
    </v-card>
</template>

<style>
@keyframes blink {
    0% { background-color: #ffeb3b; } /* Желтый */
    50% { background-color: transparent; }
    100% { background-color: #ffeb3b; }
}

.closed {
    animation: blink 1s infinite; /* 1 секунда, бесконечно */
}
.flex-space {
    display: flex;
    justify-content: space-between;
}
.flex-space button {
    font-size: 14px !important;
}
</style>
