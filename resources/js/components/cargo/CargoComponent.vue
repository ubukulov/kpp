<script>
import CargoOrders from "../../container/cargo/CargoOrders.vue";
import Root from "./Root.vue";

export default {
    name: "CargoComponent",
    components: {
        CargoOrders,
        Root
    },
    data() {
        return {
            tab: null,
            auto_tabs: null,
        }
    }
}
</script>

<template>
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
                    Корешки
                    <v-icon>mdi-history</v-icon>
                </v-tab>

                <v-tab href="#auto-history">
                    История
                    <v-icon>mdi-history</v-icon>
                </v-tab>

                <v-tabs-items v-model="auto_tabs">
                    <v-tab-item :value="'auto'">
                        <CargoOrders></CargoOrders>
                    </v-tab-item>

                    <v-tab-item :value="'koreshok'">
                        <Root></Root>
                    </v-tab-item>

                    <v-tab-item :value="'auto-history'">

                    </v-tab-item>
                </v-tabs-items>

            </v-tabs>

        </v-container>
    </v-app>
</template>

<style scoped>

</style>
