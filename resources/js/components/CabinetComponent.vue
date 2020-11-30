<template>
    <v-app id="inspire">


        <v-navigation-drawer
            v-model="drawer"
            app
        >
            <v-sheet
                color="grey lighten-4"
                class="pa-4"
            >
                <v-img
                    lazy-src="/img/logo.png"
                    max-height="289"
                    max-width="335"
                    src="/img/logo.png"
                ></v-img>
            </v-sheet>

            <v-divider></v-divider>

            <v-list>
                <v-list-item
                    v-for="[icon, text] in links"
                    :key="icon"
                    link
                >
                    <v-list-item-icon>
                        <v-icon>{{ icon }}</v-icon>
                    </v-list-item-icon>

                    <v-list-item-content>
                        <v-list-item-title>{{ text }}</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>

        <v-main>
            <v-container
                class="py-8 px-6"
                fluid
            >
                <v-row>
                    <v-col cols="12">
                        <v-card>
                            <div class="col-md-12 right_content">
                                <div>
                                    <h3>Список водителей</h3>
                                </div>
                                <hr>
                                <v-data-table
                                    :headers="headers"
                                    :items="drivers"
                                    item-key="name"
                                    class="elevation-1"
                                    :search="search"
                                    :custom-filter="filterOnlyCapsText"
                                >
                                    <template v-slot:top>
                                        <v-text-field
                                            v-model="search"
                                            label="Поиск"
                                            class="mx-4"
                                        ></v-text-field>
                                    </template>
                                    <template v-slot:item.want_to_order="{ item }">
                                        <span v-if="item.want_to_order == 1">Да</span>
                                        <span v-else>Нет</span>
                                    </template>
                                    <template v-slot:item.path_docs_fac="{ item }">
                                        <a :href="'/uploads/'+item.path_docs_fac" target="_blank">ссылка</a>
                                    </template>
                                    <template v-slot:item.path_docs_back="{ item }">
                                        <a :href="'/uploads/'+item.path_docs_back" target="_blank">ссылка</a>
                                    </template>
                                </v-data-table>
                            </div>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
    import axios from 'axios'
    export default {
        data(){
            return {
                cards: ['Today', 'Yesterday'],
                drawer: null,
                search: '',
                calories: '',
                links: [
                    ['mdi-inbox-arrow-down', 'Список отчетов'],
                    ['mdi-send', 'Заказать пропуск'],
                    ['mdi-file', 'Услуги'],
                    ['mdi-car-lifted-pickup', 'Водители'],
                    ['mdi-map-search', 'Отследить грузов'],
                    ['mdi-logout', 'Выход'],
                ],
                drivers: []
            }
        },
        computed: {
            headers () {
                return [
                    {
                        text: 'ИД',
                        align: 'start',
                        sortable: true,
                        value: 'id',
                    },
                    {
                        text: 'ФИО',
                        align: 'start',
                        sortable: true,
                        value: 'fio',
                    },
                    {
                        text: '#вод.удос.',
                        value: 'ud_number',
                        filter: value => {
                            if (!this.ud_number) return true

                            return value < parseInt(this.ud_number)
                        },
                    },
                    { text: 'Хочеть получить заказ?', value: 'want_to_order' },
                    { text: 'Телефон', value: 'phone' },
                    { text: 'Фото(лицевая)', value: 'path_docs_fac' },
                    { text: 'Фото(обратная)', value: 'path_docs_back' },
                ]
            },
        },
        methods: {
            getDrivers(){
                axios.get('/cabinet/get-drivers-list')
                .then(res => {
                    console.log(res);
                    this.drivers = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
            }
        },
        created(){
            this.getDrivers();
        }
    }
</script>
