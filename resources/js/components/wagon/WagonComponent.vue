<script>
import WagonOrders from "../../components/wagon/WagonOrders.vue"
import WagonShift from "../../components/wagon/WagonShift.vue"
import WagonStockComponent from "../../components/wagon/WagonStockComponent.vue"
import WagonReports from "../../components/wagon/WagonReports.vue";

export default {
    name: "WagonComponent",
    components: {
        WagonOrders,
        WagonStockComponent,
        WagonShift,
        WagonReports
    },
    data() {
        return {
            tab: null,
            wagon_tabs: null,
        }
    },
    watch: {
        wagon_tabs(newTab) {
            if (newTab === 'orders' && this.$refs.orders) {
                this.$refs.orders.getWagonTasks();
            }
        }
    },
    mounted() {
        this.$nextTick(() => {
            if (this.$refs.orders) {
                this.$refs.orders.getWagonTasks();
            }
        });
    },
    created() {
        if(this.$store.getters.isDispatcher) {
            this.wagon_tabs = 'stocks';
        } else {
            this.wagon_tabs = 'orders';
        }
    }
}
</script>

<template>
    <v-card>
        <v-tabs
            v-model="wagon_tabs"
        >
            <v-tabs-slider></v-tabs-slider>

            <v-tab v-if="this.$store.getters.isReceiverDeliverer" href="#orders">
                <v-icon dense x-small>mdi-file-document-multiple</v-icon>&nbsp;
                Подача/Уборка
            </v-tab>

            <v-tab href="#stocks">
                <v-icon dense x-small>mdi-warehouse</v-icon>&nbsp;
                Положение
            </v-tab>

            <v-tab v-if="this.$store.getters.isDispatcher" href="#shifts">
                <v-icon dense x-small>mdi-warehouse</v-icon>&nbsp;
                Смены
            </v-tab>

            <v-tab v-if="this.$store.getters.isReceiverDeliverer" href="#reports">
                <v-icon dense x-small>mdi-warehouse</v-icon>&nbsp;
                Отчеты
            </v-tab>

            <v-tabs-items v-model="wagon_tabs">
                <v-tab-item v-if="this.$store.getters.isReceiverDeliverer" :value="'orders'">
                    <WagonOrders ref="orders"></WagonOrders>
                </v-tab-item>

                <v-tab-item :value="'stocks'">
                    <WagonStockComponent></WagonStockComponent>
                </v-tab-item>

                <v-tab-item v-if="this.$store.getters.isDispatcher" :value="'shifts'">
                    <WagonShift></WagonShift>
                </v-tab-item>

                <v-tab-item :value="'reports'">
                    <WagonReports></WagonReports>
                </v-tab-item>
            </v-tabs-items>

        </v-tabs>
    </v-card>
</template>

<style scoped>

</style>
