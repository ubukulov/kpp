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
        },
        created(){
            this.getContainerTaskLogs();
        }
    }
</script>

<style scoped>

</style>
