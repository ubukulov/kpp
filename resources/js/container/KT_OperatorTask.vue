<template>
    <v-app>
        <template>
            <v-card>
                <v-tabs
                    v-model="tab"
                    background-color="deep-purple accent-4"
                    dark
                    icons-and-text
                >
                    <v-tabs-slider></v-tabs-slider>

                    <v-tab href="#tab-1">
                        Контейнер
                        <v-icon>mdi-train</v-icon>
                    </v-tab>

                    <v-tab href="#tab-2">
                        Авто
                        <v-icon>mdi-car-multiple</v-icon>
                    </v-tab>

                    <v-tab href="#tab-3">
                        Груз
                        <v-icon>mdi-truck</v-icon>
                    </v-tab>
                </v-tabs>

                <v-tabs-items v-model="tab">
                    <v-tab-item
                        :value="'tab-1'"
                    >
                        <Container></Container>
                    </v-tab-item>

                    <v-tab-item
                        :value="'tab-2'"
                    >
                        <v-app>
                            <v-container>

                                <v-tabs
                                    v-model="auto_tabs"
                                    background-color="deep-orange-accent-1"
                                    dark
                                    icons-and-text
                                >
                                    <v-tabs-slider></v-tabs-slider>

                                    <v-tab href="#auto">
                                        Заявки
                                        <v-icon>mdi-file-document-multiple</v-icon>
                                    </v-tab>

                                    <v-tab href="#koreshok">
                                        Список корешков
                                        <v-icon>mdi-history</v-icon>
                                    </v-tab>

                                    <v-tab href="#auto-history">
                                        История
                                        <v-icon>mdi-history</v-icon>
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

                            </v-container>
                        </v-app>
                    </v-tab-item>

                    <v-tab-item
                        :value="'tab-3'"
                    >
                        <CargoOrders></CargoOrders>
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
    export default {
        components: {
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
        }
    }
</script>

<style scoped>
    .allow {
        font-size: 20px; color: red; box-shadow: 0 0 0 0 rgba(0, 0, 0, 1);
        transform: scale(1); animation: pulse 2s infinite; border-radius: 50%;
    }
</style>
