<script>
import dateformat from "dateformat";
export default {
    data() {
        return {
            cargo_tasks: [],
        }
    },
    props: [
        'state'
    ],
    methods: {
        convertDateToOurFormat(dt){
            return dateformat(dt, 'dd.mm.yyyy HH:MM');
        },
        whatClassBeUsed(task_type, hasAnyPositionCancelOrEdit){
            if (hasAnyPositionCancelOrEdit) {
                return '#0000FF';
            }
            return (task_type === 'receive') ? '#006600' : '#952175';
        },
        getCargoTasks() {
            axios.get(`/cabinet/webcont/cargo-controller/get-cargo-tasks?status=${this.state}`)
                .then(res => {
                    console.log(res)
                    this.cargo_tasks = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
        },
        handleClick(id){
            window.location.href = `/cabinet/webcont/cargo-controller/cargo-task/${id}/show`;
        }
    },
    watch: {
        state() {
            this.getCargoTasks();
        }
    },
    created() {
        this.getCargoTasks();
    }
}
</script>

<template>
    <v-container>
        <v-row>
            <v-col
                v-for="(item, i) in cargo_tasks"
                :key="i"
                cols="12"
            >
                <v-card
                    :color="whatClassBeUsed(item.type, item.hasAnyPositionCancelOrEdit)"
                    dark
                    @click="handleClick(item.id)"
                >
                    <v-card-text>
                        <div>
                            <strong>Компания</strong>
                            <p>{{ item.company.short_en_name }}</p>
                        </div>

                        <div class="card-body-block card-flex">
                            <div>
                                <strong>Номер</strong>
                                <p>{{ item.number }}</p>
                            </div>
                            <div>
                                <strong>Тип</strong>
                                <p v-if="item.type === 'receive'">ЗАВОЗ</p>
                                <p v-if="item.type === 'ship'">ВЫВОЗ</p>
                            </div>

                            <div>
                                <strong>Статус</strong>
                                <p v-if="item.status === 'new'">Новый</p>
                                <p v-if="item.status === 'processing'">В работе</p>
                                <p v-if="item.status === 'completed'">Завершен</p>
                                <p v-if="item.status === 'canceled'">Отменен</p>
                                <p v-if="item.status === 'failed'">Ошибка</p>
                            </div>

                            <div>
                                <strong>Машина</strong>
                                <p>{{ item.spine?.car_number ?? '' }}</p>
                            </div>
                        </div>

                        <div class="card-footer-block card-flex">
                            <div style="width: 50%">
                                        <span v-if="item.user" class="subheading mr-2">
                                            {{ item.user.full_name }}
                                        </span>
                            </div>
                            <div style="width: 50%;">
                                        <span class="subheading" style="float: right">
                                            {{ convertDateToOurFormat(item.created_at) }}
                                        </span>
                            </div>

                        </div>


                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<style scoped>

</style>
