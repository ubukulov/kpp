<template>
    <v-app>
        <v-main>
            <v-container>
                <v-card
                    max-width="400"
                    class="mx-auto"
                >
                    <v-container>
                        <v-row dense>
                            <v-col cols="12">
                                <v-container>
                                    <v-row style="background: whitesmoke;padding: 10px;">
                                        <div style="width: 80%">
                                            {{ name }}
                                        </div>
                                        <div style="width: 20%;">
                                <span style="float: right; font-size: 14px;">
                                    <a href="/logout">Выйти</a>
                                </span>
                                        </div>
                                    </v-row>
                                </v-container>
                            </v-col>

                            <v-col
                                class="d-flex mb-3"
                                cols="12"
                                sm="6"
                                v-if="bottom_nav ==='tasks'"
                            >
                                <v-select
                                    :items="filters"
                                    :hint="`${filters.id}, ${filters.title}`"
                                    item-value="id"
                                    v-model="filter_id"
                                    @change="getContainerTasks()"
                                    item-text="title"
                                ></v-select>
                            </v-col>

                            <v-row v-if="bottom_nav ==='tasks'">
                                <v-col
                                    v-for="(item, i) in container_tasks"
                                    :key="i"
                                    cols="12"
                                >
                                    <v-card
                                        :color="whatClassBeUsed(item.task_type, item.hasAnyPositionCancelOrEdit)"
                                        dark
                                    >
                                        <v-card-title
                                            class="text-h5"
                                        >
                                            <div style="font-size: 18px;">
                                                {{ item.number }}
                                            </div>
                                            <div>
                                                <span style="float: right; font-size: 14px;">
                                                    Выполнено: {{ item.stat }}
                                                </span>
                                            </div>

                                        </v-card-title>

                                        <v-card-subtitle>
                                            <v-container>
                                                <v-row>
                                                    <div style="width: 33%">
                                        <span v-if="item.status==='open'" class="subheading mr-2">
                                            В работе <a :href="'/container-controller/task/'+item.id+'/container-logs'">
                                            <i :class="[{ allow: item.allow}]" style="font-size: 20px;" class="fa fa-history"></i></a>
                                        </span>
                                                        <span v-else class="subheading mr-2">
                                            Выполнен <a :href="'/container-controller/task/'+item.id+'/container-logs'">
                                            <i style="font-size: 20px;" class="fa fa-history"></i></a>
                                        </span>
                                                    </div>

                                                    <div style="width: 33%; text-align: center;">
                                        <span class="subheading mr-2">
                                            <v-icon>
                                                mdi-tag
                                            </v-icon>
                                            {{ item.type }}
                                        </span>
                                                    </div>
                                                    <div style="width: 33%;">
                                        <span class="subheading" style="float: right">
                                            <v-icon v-if="item.trans_type==='auto'">
                                                mdi-car
                                            </v-icon>
                                            <v-icon v-else>
                                                mdi-train
                                            </v-icon>
                                            {{ item.trans }}
                                        </span>
                                                    </div>
                                                </v-row>
                                            </v-container>

                                        </v-card-subtitle>

                                        <v-card-text>

                                        </v-card-text>

                                        <v-card-text>
                                            <v-container>
                                                <v-row>
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
                                                </v-row>
                                            </v-container>
                                        </v-card-text>
                                    </v-card>
                                </v-col>

                                <v-col cols="12">
                                    <nav aria-label="...">
                                        <ul class="pagination">
                                            <li class="page-item" v-bind:class="[{disabled: !pagination.prev_page_url}]">
                                                <a class="page-link" v-on:click="getContainerTasks(pagination.prev_page_url)" href="#" tabindex="-1">Предыдущая</a>
                                            </li>
                                            <li class="page-item" v-for="page in pagination.last_page" v-bind:class="[{ disabled: page === pagination.current_page}]">
                                                <a class="page-link" v-if="page <= 3" href="#" v-on:click="getContainerTasks('/container-controller/get-container-tasks/'+filter_id+'?page='+page)">
                                                    {{ page }}
                                                    <span v-if="page === pagination.current_page" class="sr-only">(current)</span>
                                                </a>
                                            </li>

                                            <li class="page-item" v-bind:class="[{disabled: !pagination.next_page_url}]">
                                                <a class="page-link" v-on:click="getContainerTasks(pagination.next_page_url)" href="#">Следующая</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </v-col>
                            </v-row>

                            <v-row class="mt-2" v-if="bottom_nav ==='search'">
                                <v-col cols="8">
                                    <v-text-field
                                        v-model="container_number"
                                        label="Номер контейнера"
                                        hide-details="auto"
                                    ></v-text-field>
                                </v-col>

                                <v-col cols="4">
                                    <v-btn
                                        depressed
                                        color="primary"
                                        @click="getInfoForContainer()"
                                    >
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </v-btn>
                                </v-col>

                                <v-divider></v-divider>

                                <v-col v-if="info_container !== ''" cols="12">
                                    <div style="font-size: 20px;
                        line-height: 20px;
                        margin-bottom: 30px;
                        font-weight: bold;
                        border: 1px solid yellowgreen;
                        text-align: center;
                        padding: 10px;" v-html="info_container">

                                    </div>

                                    <v-btn
                                        color="primary"
                                        style="font-size: 14px !important; width: 300px;"
                                        v-if="system.error"
                                    >
                                        <v-icon>mdi-briefcase-edit</v-icon> &nbsp;Изменить номер контейнера
                                    </v-btn>
                                </v-col>
                            </v-row>

                            <v-row style="padding-right: 0;" class="mt-2" v-if="bottom_nav ==='schedule'">
                                <v-col cols="12" style="padding: 0;">
                                    <List></List>
                                </v-col>
                            </v-row>
                        </v-row>

                        <v-overlay :value="overlay">
                            <v-progress-circular
                                indeterminate
                                size="64"
                            ></v-progress-circular>
                        </v-overlay>
                    </v-container>
                </v-card>
            </v-container>
        </v-main>

        <v-footer app>
            <v-bottom-navigation v-model="bottom_nav">
                <v-btn value="tasks">
                    <span style="font-size: 12px;">Заявки</span>

                    <v-icon>mdi-history</v-icon>
                </v-btn>

                <v-btn value="search">
                    <span style="font-size: 12px;">Поиск</span>

                    <v-icon>mdi-search-web</v-icon>
                </v-btn>

                <v-btn value="schedule">
                    <span style="font-size: 12px;">График</span>

                    <v-icon>mdi-calendar-check</v-icon>
                </v-btn>
            </v-bottom-navigation>
        </v-footer>
    </v-app>
</template>

<script>
    import axios from "axios";
    import dateformat from "dateformat";
    import List from "./List.vue";
    export default {
        components: {
            List
        },
        data: () => ({
            container_tasks: [],
            overlay: false,
            filters: [
                {
                    'title': 'Все открытые заявки:',
                    'id': 1
                },
                {
                    'title': 'Все закрытые заявки',
                    'id': 4
                },
            ],
            filter_id: 1,
            isAllow: false,
            pagination: {},
            bottom_nav: 'tasks',
            container_number: '',
            info_container: '',
            system: {
                success: false,
                error: false,
            }
        }),
        props: ['name'],
        methods: {
            getContainerTasks(url){
                let pagination;
                this.container_tasks = [];
                this.overlay = true;
                url = url || "/container-controller/get-container-tasks/" + this.filter_id;
                axios.get(url)
                    .then(res => {
                        console.log(res);
                        this.container_tasks = res.data.data;
                        this.overlay = false;
                        pagination = {
                            prev_page_url: res.data.links.prev,
                            next_page_url: res.data.links.next,
                            last_page_url: res.data.links.last,
                            first_page_url: res.data.links.first,
                            current_page: res.data.meta.current_page,
                            last_page: res.data.meta.last_page
                        };
                        this.pagination = pagination;
                    })
                    .catch(err => {
                        this.overlay = false;
                        console.log(err);
                    })
            },
            convertDateToOurFormat(dt){
                return dateformat(dt, 'dd.mm.yyyy HH:MM');
            },
            print_r(id){
                window.location.href = '/container-terminals/task/'+id+'/print'
            },
            whatClassBeUsed(task_type, hasAnyPositionCancelOrEdit){
                if (hasAnyPositionCancelOrEdit) {
                    return '#0000FF';
                }
                return (task_type === 'receive') ? '#006600' : '#952175';
            },
            getInfoForContainer(){
                this.overlay = true;
                let formData = new FormData();
                formData.append('container_number', this.container_number);
                axios.post('/get-info-for-container', formData)
                    .then(res => {
                        this.overlay = false;
                        this.system.success = true;
                        this.info_container = res.data.data.text;
                        this.container_number = res.data.data.container_number;
                        console.log(res.data)
                    })
                    .catch(err => {
                        if(err.response.status === 404 || err.response.status === 403) {
                            this.info_container = err.response.data;
                            this.overlay = false;
                        }
                        this.system.success = false;
                        console.log(err.response)
                    })
            }
        },
        created(){
            this.getContainerTasks();
        }
    }
</script>

<style scoped>
    .v-card__title {
        justify-content: space-between;
    }
</style>
