<template>
    <v-card
        max-width="400"
        class="mx-auto"
    >
        <v-container>
            <v-row dense>
                <v-col>
                    <v-container>
                        <v-row style="background: whitesmoke;padding: 10px;">
                            <div style="width: 20%">
                                <a href="/container-controller">Назад</a>
                            </div>
                            <div style="width: 80%;">
                                <span style="float: right; font-size: 14px;">
                                    Заявка: {{ container_task.number }} - {{ container_task.type }} - {{ container_task.trans }}
                                </span>
                            </div>
                        </v-row>
                    </v-container>
                </v-col>

                <v-col
                    v-for="(item, i) in import_logs"
                    :key="i"
                    cols="12"
                >
                    <v-card
                        color="#000000"
                        dark
                    >
                        <v-card-title
                            class="text-h5"
                        >
                            <v-container>
                                <v-row>
                                    <div style="width: 100%">
                                        {{ item.container_number }}
                                    </div>
                                </v-row>
                            </v-container>

                        </v-card-title>

                        <v-card-subtitle>
                            <v-container>
                                <v-row>
                                    <div style="width: 55%">
                                        <div v-if="container_task.task_type === 'receive'">
                                            <div v-if="item.state === 'posted'">
                                                <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ posted }}
                                            </div>

                                            <div v-else>
                                                <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp; {{ not_posted }}
                                            </div>
                                        </div>

                                        <div v-if="container_task.task_type === 'ship'">
                                            <div v-if="item.state === 'selected'">
                                                <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ selected }}
                                            </div>

                                            <div v-else-if="item.state === 'issued'">
                                                <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ issued }}
                                            </div>

                                            <div v-else>
                                                <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp; {{ not_selected }}
                                            </div>
                                        </div>
                                    </div>

                                    <div style="width: 45%; text-align: right;">
                                        <span class="subheading mr-2">
                                            <v-icon>
                                                mdi-tag
                                            </v-icon>
                                            {{ item.address }}
                                        </span>
                                    </div>

                                    <div style="width: 100%;" v-if="item.position.cancel">
                                        <v-divider></v-divider>
                                        <v-row>
                                            <v-col cols="12">
                                                <p style="color: red;">Удаление позиции из заявки</p>
                                                <p>Причина: <br>{{ item.position.reason }}</p>
                                            </v-col>

                                            <v-col>
                                                <v-btn @click="rejectCancelPosition(item.container_task_id, item.container_number)" style="font-size: 10px !important;">Отменить</v-btn>
                                                <v-btn @click="confirmCancelPosition(item.container_task_id, item.container_number, item.id)" style="font-size: 10px !important; float: right; background-color: green">Подтвердить</v-btn>
                                            </v-col>
                                        </v-row>
                                    </div>

                                    <div style="width: 100%;" v-if="item.position.edit">
                                        <v-divider></v-divider>
                                        <v-row>
                                            <v-col cols="12">
                                                <p style="color: red;">Редактирование позиции из заявки</p>
                                                <p>Номер контейнера (сейчас): {{ item.container_number }}</p>
                                                <p>Номер контейнера (будет) : {{ item.position.new_container_number }}</p>
                                            </v-col>

                                            <v-col>
                                                <v-btn @click="rejectEditPosition(item.container_task_id, item.container_number)" style="font-size: 10px !important;">Отменить</v-btn>
                                                <v-btn @click="confirmEditPosition(item.container_task_id, item.container_number, item.id, item.position.new_container_number)" style="font-size: 10px !important; float: right; background-color: green">Подтвердить</v-btn>
                                            </v-col>
                                        </v-row>
                                    </div>
                                </v-row>
                            </v-container>

                        </v-card-subtitle>
                    </v-card>
                </v-col>
            </v-row>

            <v-overlay :value="overlay">
                <v-progress-circular
                    indeterminate
                    size="64"
                ></v-progress-circular>
            </v-overlay>
        </v-container>
    </v-card>
</template>

<script>
    import axios from "axios";

    export default {
        data: () => ({
            import_logs: [],
            overlay: false,
        }),
        props: ['container_task', 'posted', 'not_posted', 'not_selected', 'selected', 'issued'],
        methods: {
            getContainerTaskLogs(){
                this.import_logs = [];
                this.overlay = true;
                axios.get('/container-controller/task/'+this.container_task.id+'/import-logs')
                    .then(res => {
                        console.log(res);
                        this.import_logs = res.data;
                        this.overlay = false;
                    })
                    .catch(err => {
                        console.log(err);
                    })
            },
            rejectCancelPosition(container_task_id, container_number){
                let formData = new FormData();
                formData.append('container_task_id', container_task_id);
                formData.append('container_number', container_number);
                this.overlay = true;
                axios.post('/container-controller/task/reject-cancel-position', formData)
                .then(res => {
                    this.overlay = false;
                    console.log(res);
                    this.getContainerTaskLogs()
                })
                .catch(err => {
                    this.overlay = false;
                    console.log(err)
                })
            },
            confirmCancelPosition(container_task_id, container_number, import_log_id){
                let formData = new FormData();
                formData.append('container_task_id', container_task_id);
                formData.append('container_number', container_number);
                formData.append('import_log_id', import_log_id);
                this.overlay = true;
                axios.post('/container-controller/task/confirm-cancel-position', formData)
                    .then(res => {
                        this.overlay = false;
                        console.log(res);
                        this.getContainerTaskLogs();
                    })
                    .catch(err => {
                        console.log(err);
                        this.overlay = false;
                    })
            },
            rejectEditPosition(container_task_id, container_number){
                let formData = new FormData();
                formData.append('container_task_id', container_task_id);
                formData.append('container_number', container_number);
                this.overlay = true;
                axios.post('/container-controller/task/reject-edit-position', formData)
                    .then(res => {
                        this.overlay = false;
                        console.log(res);
                        this.getContainerTaskLogs()
                    })
                    .catch(err => {
                        this.overlay = false;
                        console.log(err)
                    })
            },
            confirmEditPosition(container_task_id, container_number, import_log_id, new_container_number){
                let formData = new FormData();
                formData.append('container_task_id', container_task_id);
                formData.append('container_number', container_number);
                formData.append('new_container_number', new_container_number);
                formData.append('import_log_id', import_log_id);
                this.overlay = true;
                axios.post('/container-controller/task/confirm-edit-position', formData)
                    .then(res => {
                        this.overlay = false;
                        console.log(res);
                        this.getContainerTaskLogs();
                    })
                    .catch(err => {
                        console.log(err);
                        this.overlay = false;
                    })
            }
        },
        created(){
            this.getContainerTaskLogs();
        }
    }
</script>

<style scoped>

</style>
