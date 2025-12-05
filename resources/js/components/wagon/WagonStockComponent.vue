<script>
import WagonOrders from "../../components/wagon/WagonOrders.vue"
import WagonStocks from "../../components/wagon/WagonStocks.vue"
import axios from "axios";

export default {
    name: "WagonStockComponent",
    components: {
        WagonStocks
    },
    data() {
        return {
            tab: null,
            wagon_tabs: 'ilc',
            wagonTracks: [],
        }
    },
    methods: {
        getWagonTracks() {
            axios.get('/container-terminals/wagon/get-wagon-tracks')
                .then(res => {
                    this.wagonTracks = res.data;
                })
                .catch(err => {
                    console.log(err);
                })
        },
    },
    computed: {
        isShiftNotAssigned() {
            if(this.$store.getters.isDispatcher) {
                return !this.$store.getters.currentShifts || this.$store.getters.currentShifts.length === 0;
            }
            return false;
        }
    },
    watch: {
        wagon_tabs(newTab) {
            this.$nextTick(() => {
                if (newTab === 'ilc' && this.$refs.ilc) {
                    this.$refs.ilc.getWagonStocks();
                }
                if (newTab === 'resident' && this.$refs.resident) {
                    this.$refs.resident.getWagonStocks();
                }
                if (newTab === 'warehouse' && this.$refs.warehouse) {
                    this.$refs.warehouse.getWagonStocks();
                }
                if (newTab === 'all' && this.$refs.all) {
                    this.$refs.all.getWagonStocks();
                }
            });
        }
    },
    created() {
        this.getWagonTracks();
    }
}
</script>

<template>
    <div>
        <div v-if="isShiftNotAssigned" style="text-align: center;">
            <h2 style="color: red;">Не забудьте смены назначить!</h2>
        </div>
        <v-card>
            <v-tabs
                v-model="wagon_tabs"
            >
                <v-tabs-slider></v-tabs-slider>

                <v-tab href="#ilc">
                    <v-icon dense x-small>mdi-file-document-multiple</v-icon>&nbsp;
                    ILC
                </v-tab>

                <v-tab href="#resident">
                    <v-icon dense x-small>mdi-warehouse</v-icon>&nbsp;
                    Резиденты
                </v-tab>

                <v-tab href="#warehouse">
                    <v-icon dense x-small>mdi-warehouse</v-icon>&nbsp;
                    Склады
                </v-tab>

                <v-tab href="#all">
                    <v-icon dense x-small>mdi-warehouse</v-icon>&nbsp;
                    Все
                </v-tab>

                <v-tabs-items v-model="wagon_tabs">
                    <v-tab-item :value="'ilc'">
                        <WagonStocks ref="ilc" group="ilc" :wagon-tracks="wagonTracks"></WagonStocks>
                    </v-tab-item>

                    <v-tab-item :value="'resident'">
                        <WagonStocks ref="resident" group="resident" :wagon-tracks="wagonTracks"></WagonStocks>
                    </v-tab-item>

                    <v-tab-item :value="'warehouse'">
                        <WagonStocks ref="warehouse" group="warehouse" :wagon-tracks="wagonTracks"></WagonStocks>
                    </v-tab-item>

                    <v-tab-item :value="'all'">
                        <WagonStocks ref="all" group="all" :wagon-tracks="wagonTracks"></WagonStocks>
                    </v-tab-item>
                </v-tabs-items>
            </v-tabs>
        </v-card>
    </div>
</template>

<style scoped>

</style>
