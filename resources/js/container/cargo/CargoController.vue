<script>
import CargoOrdersList from "../../components/cargo/CargoOrdersList.vue";
export default {
    components: {
        CargoOrdersList
    },
    data() {
        return {
            tab: 'tab-1',
        }
    },
    props: [
        'user'
    ],
    computed: {
        tabState() {
            if (this.tab === 'tab-1') return 'new';
            if (this.tab === 'tab-2') return 'processing';
            if (this.tab === 'tab-3') return 'completed';
        }
    }
}
</script>

<template>
<v-app>
    <v-main>

        <v-tabs
            v-model="tab"
            background-color="deep-purple accent-4"
            centered
            dark
            icons-and-text
        >
            <v-tabs-slider></v-tabs-slider>

            <v-tab href="#tab-1">
                Новый
                <v-icon>mdi-file-document</v-icon>
            </v-tab>

            <v-tab href="#tab-2">
                В работе
                <v-icon>mdi-file-clock-outline</v-icon>
            </v-tab>

            <v-tab href="#tab-3">
                Завершен
                <v-icon>mdi-file-check</v-icon>
            </v-tab>

            <v-tabs-items v-model="tab">
                <v-tab-item
                    :value="'tab-1'"
                >
                    <CargoOrdersList :state="tabState"></CargoOrdersList>
                </v-tab-item>

                <v-tab-item
                    :value="'tab-2'"
                >
                    <CargoOrdersList :state="tabState"></CargoOrdersList>
                </v-tab-item>

                <v-tab-item
                    :value="'tab-3'"
                >
                    <CargoOrdersList :state="tabState"></CargoOrdersList>
                </v-tab-item>
            </v-tabs-items>
        </v-tabs>
    </v-main>
</v-app>
</template>

<style scoped>
.card-flex {
    display: flex;
    justify-content: space-between;
}
</style>
