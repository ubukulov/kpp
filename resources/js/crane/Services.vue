<template>
    <v-container>
        <v-row v-if="services.home">
            <v-col cols="12">
                <h4>Сервисы</h4>
            </v-col>
            <v-col @click="showStatistics" cols="4">
                <v-icon class="service_icon" x-large light>mdi-percent</v-icon>
                <p>Статистика</p>
            </v-col>

            <v-col @click="showCrane" cols="4">
                <v-icon class="service_icon" x-large>mdi-crane</v-icon>
                <p>Кран</p>
            </v-col>
        </v-row>

        <v-row>
            <v-col v-if="services.stats" cols="12">
                <kt-crane-stats @back="back"></kt-crane-stats>
            </v-col>

            <v-col v-if="services.crane" cols="12">
                <crane @back="back"></crane>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
    export default {
        data(){
            return {
                services: {
                    home: true,
                    stats: false,
                    crane: false
                }
            }
        },
        methods: {
            showStatistics() {
                this.services.stats = true;
                this.services.home = false;
            },
            back(){
                Object.keys(this.services).forEach(key => {
                    if(key === 'home') {
                        this.services[key] = true;
                    } else {
                        this.services[key] = false;
                    }
                });
            },
            showCrane(){
                this.services.crane = true;
                this.services.home = false;
            }
        }
    }
</script>

<style scoped>
    .service_icon {
        color: #6AC22B;
    }
    h4 {
        text-align: left;
    }
</style>
