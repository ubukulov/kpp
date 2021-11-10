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


                <v-col
                    v-for="(item, i) in container_tasks"
                    :key="i"
                    cols="12"
                >
                    <v-card
                        :color="(item.task_type === 'receive') ? '#006600' : '#952175'"
                        dark
                    >
                        <v-card-title
                            class="text-h5"
                        >
                            <v-container>
                                <v-row>
                                    <div style="width: 50%">
                                        {{ item.number }}
                                    </div>
                                    <div style="width: 50%;">
                                        <span style="float: right; font-size: 14px;">
                                            Выполнено: {{ item.stat }}
                                        </span>
                                    </div>
                                </v-row>
                            </v-container>

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
                                        <span class="subheading mr-2">
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

                <v-col>
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
    import dateformat from "dateformat";

    export default {
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
            pagination: {}
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

</style>
