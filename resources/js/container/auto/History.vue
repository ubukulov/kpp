<script>
import axios from "axios";
import dateformat from "dateformat";

export default {
    data() {
        return {
            vin_code: null,
            system: {
                success: false,
                error: false,
            },
            technique_logs: [],
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
        getTechniqueLogs(){
            axios.get(`/container-terminals/technique/${this.vin_code}/get-logs/`)
                .then(res => {
                    console.log(res);
                    this.technique_logs = res.data;
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
    }
}
</script>

<template>
    <v-app>
        <v-container>
            <v-row class="mt-4">
                <v-col cols="5">
                    <v-text-field
                        label="Введите VINCODE"
                        v-model="vin_code"
                    ></v-text-field>

                </v-col>

                <v-col cols="2">
                    <v-btn
                        class="btn success"
                        @click="getTechniqueLogs()"
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
                                v-for="(item, i) in technique_logs"
                                :key="i"
                                color="orange"
                                :right="true"
                            >
                                <template v-slot:opposite>
                                                    <span
                                                        :class="`headline font-weight-bold orange--text`"
                                                        v-text="convertDateToOurFormat(item.created_at)"
                                                    ></span>
                                </template>

                                <div class="py-4">
                                    <h2 :class="`headline font-weight-light mb-4 orange--text`">
                                        {{ returnValueFromArray(item.operation_type).value }}
                                    </h2>
                                    <div>
                                        <div><strong>Пользователь:</strong> {{ item.full_name }}</div>
                                        <div><strong>Телефон:</strong> {{ item.phone }}</div>
                                        <div><strong>Клиент:</strong> {{ item.owner }}</div>
                                        <div><strong>ВИНКОД:</strong> {{ item.vin_code }}</div>
                                        <div><strong>ЦВЕТ:</strong> {{ item.color }}</div>
                                        <div><strong>Марка:</strong> {{ item.mark }}</div>
                                        <div v-if="item.operation_type === 'completed'"><strong>Корешок:</strong> {{ item.spine_number }}</div>
                                        <table class="table table-bordered mt-2">
                                            <thead>
                                            <th>Из</th>
                                            <th>В</th>
                                            </thead>

                                            <tbody>
                                            <tr>
                                                <td>
                                                    <span>{{ item.address_from }}</span>
                                                </td>
                                                <td>
                                                    <span>{{ item.address_to }}</span>
                                                </td>
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
</template>

<style scoped>

</style>
