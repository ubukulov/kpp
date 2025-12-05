<template>
    <v-app>
        <template>
            <v-card>
                <v-toolbar flat dark color="light-green">
                    <!-- Слева -->
                    <img
                        src="/img/ilc_logo.png"
                        alt="Лого"
                        class="ml-2"
                        style="height: 40px; filter: brightness(0) invert(1);"
                    />

                    <!-- Центр (табы) -->
                    <v-spacer></v-spacer>

                    <v-tabs
                        v-model="tab"
                        background-color="light-green"
                        centered
                        dark
                    >
                        <v-tabs-slider></v-tabs-slider>

                        <v-tab href="#tab-1">
                            <v-icon dense small>mdi-train</v-icon>&nbsp;
                            Контейнер
                        </v-tab>


                        <v-tab href="#tab-2">
                            <v-icon dense small>mdi-car-multiple</v-icon>&nbsp;
                            Авто
                        </v-tab>

                        <v-tab href="#tab-3">
                            <v-icon dense small>mdi-truck</v-icon>&nbsp;
                            Груз
                        </v-tab>

                        <v-tab href="#tab-4">
                            <v-icon dense small>mdi-set-top-box</v-icon>&nbsp;
                            Вагон
                        </v-tab>
                    </v-tabs>



                    <v-spacer></v-spacer>

                    <!-- Справа -->
                    <v-menu offset-y>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn
                                v-bind="attrs"
                                v-on="on"
                                class="d-flex align-center"
                                text
                            >
                                <!-- Аватар -->
                                <v-avatar size="32" class="mr-2">
                                    <v-img :src="user.image || '/img/default-user-image.png'" alt="Аватар"></v-img>
                                </v-avatar>

                                <!-- Имя + должность -->
                                <div class="d-flex flex-column align-start mr-1">
                                    <span class="text-white text-body-2">{{ user?.full_name }}</span>
                                    <span class="text-caption text-white text--secondary">{{ user?.position?.title || '' }}</span>
                                </div>

                                <!-- Стрелка вниз -->
                                <v-icon small>mdi-chevron-down</v-icon>
                            </v-btn>
                        </template>

                        <v-list>
                            <v-list-item @click="onLogout">
                                <v-list-item-title>Выход</v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                </v-toolbar>

                <v-tabs-items v-model="tab">
                    <v-tab-item
                        :value="'tab-1'"
                    >
                        <Container></Container>
                    </v-tab-item>

                    <v-tab-item
                        :value="'tab-2'"
                    >
                        <v-card>
                            <v-tabs
                                v-model="auto_tabs"
                            >
                                <v-tabs-slider></v-tabs-slider>

                                <v-tab href="#auto">
                                    <v-icon dense x-small>mdi-file-document-multiple</v-icon>&nbsp;
                                    Заявки
                                </v-tab>

                                <v-tab href="#koreshok">
                                    <v-icon dense x-small>mdi-history</v-icon>&nbsp;
                                    Список корешков
                                </v-tab>

                                <v-tab href="#auto-history">
                                    <v-icon dense x-small>mdi-history</v-icon>&nbsp;
                                    История
                                </v-tab>

                                <v-tabs-items v-model="auto_tabs">
                                    <v-tab-item :value="'auto'">
                                        <Orders></Orders>
                                    </v-tab-item>

                                    <v-tab-item :value="'koreshok'">
                                        <Documents></Documents>
                                    </v-tab-item>

                                    <v-tab-item :value="'auto-history'">
                                        <History></History>
                                    </v-tab-item>
                                </v-tabs-items>

                            </v-tabs>
                        </v-card>
                    </v-tab-item>

                    <v-tab-item
                        :value="'tab-3'"
                    >
                        <CargoComponent></CargoComponent>
                    </v-tab-item>

                    <v-tab-item
                        :value="'tab-4'"
                    >
                        <WagonComponent></WagonComponent>
                    </v-tab-item>
                </v-tabs-items>

            </v-card>
        </template>
    </v-app>
</template>

<script>
    import Container from "./Container.vue";
    import Orders from "./auto/Orders.vue";
    import Documents from "./auto/Documents.vue";
    import History from "./auto/History.vue";
    import CargoOrders from "./cargo/CargoOrders.vue";
    import CargoComponent from "../components/cargo/CargoComponent.vue";
    import WagonComponent from "../components/wagon/WagonComponent.vue";
    export default {
        components: {
            CargoComponent,
            WagonComponent,
            Container,
            Orders,
            Documents,
            History,
            CargoOrders
        },
        props: [
            'user'
        ],
        data(){
            return {
                tab: null,
                auto_tabs: null,
            }
        },
        computed: {
            avatarSrc() {
                return this.user?.image || "/img/default-user-image.png";
            }
        },
        methods: {
            onLogout(){
                window.location.href = '/logout';
            }
        }
    }
</script>

<style scoped>
    .allow {
        font-size: 20px; color: red; box-shadow: 0 0 0 0 rgba(0, 0, 0, 1);
        transform: scale(1); animation: pulse 2s infinite; border-radius: 50%;
    }
</style>
