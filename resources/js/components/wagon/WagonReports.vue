<script>
import axios from "axios";
import Loading from "../system/Loading.vue";

export default {
    components: {
        Loading
    },
    name: "WagonReports",
    data() {
        return {
            loading: false,
            menu: false,
            menu2: false,
            from_date: new Date().toISOString().slice(0, 10),
            to_date: new Date().toISOString().slice(0, 10),
        }
    },
    methods: {
        generateReportAndDownload(){
            this.loading = true;
            let formData = new FormData();

            formData.append("from_date", this.from_date);
            formData.append("to_date", this.to_date);

            axios.post('/container-terminals/wagon/generate-and-download-report', formData)
                .then(res => {
                    this.loading = false;
                    window.location.href = res.data;
                })
                .catch(err => {
                    this.loading = false;
                    console.log(err);
                })
        }
    }
}
</script>

<template>
    <v-card flat>
        <v-container>
            <v-row>
                <v-col md="3">
                    <v-menu
                        v-model="menu"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        transition="scale-transition"
                        offset-y
                        min-width="290px"
                    >
                        <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                                v-model="from_date"
                                label="C"
                                prepend-icon="event"
                                readonly
                                v-bind="attrs"
                                v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker v-model="from_date" :first-day-of-week="1" locale="ru" @input="menu = false"></v-date-picker>
                    </v-menu>
                </v-col>

                <v-col md="3">
                    <v-menu
                        v-model="menu2"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        transition="scale-transition"
                        offset-y
                        min-width="290px"
                    >
                        <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                                v-model="to_date"
                                label="ПО"
                                prepend-icon="event"
                                readonly
                                v-bind="attrs"
                                v-on="on"
                            ></v-text-field>
                        </template>
                        <v-date-picker v-model="to_date" :first-day-of-week="1" locale="ru" @input="menu2 = false"></v-date-picker>
                    </v-menu>
                </v-col>

                <v-col cols="3">
                    <v-btn
                        color="primary"
                        @click="generateReportAndDownload"
                    >
                        Скачать отчет
                    </v-btn>
                </v-col>
            </v-row>

            <Loading :show="loading"></Loading>
        </v-container>
    </v-card>
</template>

<style scoped>

</style>
