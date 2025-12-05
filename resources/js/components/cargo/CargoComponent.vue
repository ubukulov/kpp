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
                Корешки

            </v-tab>

            <v-tab href="#auto-history">
                <v-icon dense x-small>mdi-history</v-icon>&nbsp;
                История
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
    </v-card>
</template>

<style scoped>

</style>
