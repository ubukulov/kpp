<script>
import axios from "axios";
import dateformat from "dateformat";

export default {
    data() {
        return {
            search: '',
            spine_headers: [
                { text: 'Корешок', value: 'spine_number'},
                { text: 'Компания', value: 'company'},
                { text: 'Заявка', value: 'technique_task_number'},
                { text: 'Названия', value: 'name' },
                { text: 'Номер машины', value: 'car_number' },
                { text: 'ФИО водителя', value: 'driver_name' },
                { text: 'Печать', value: 'print' },
                { text: 'Дата', value: 'created_at' },
            ],
            spines: []
        }
    },
    methods: {
        convertDateToOurFormat(dt){
            return dateformat(dt, 'dd.mm.yyyy HH:MM');
        },
        getSpines(){
            axios.get('/container-terminals/get-spines')
                .then(res => {
                    this.spines = res.data;
                })
                .catch(err => {
                    console.log(err);
                })
        }
    },
    created(){
        this.getSpines();
    }
}
</script>

<template>
    <v-card flat>
        <v-card-title class="d-flex align-center pe-2">
            <v-icon icon="mdi-video-input-component"></v-icon> &nbsp;
            Список корешок

            <v-spacer></v-spacer>

            <v-text-field
                v-model="search"
                density="compact"
                label="Search"
                prepend-inner-icon="mdi-magnify"
                variant="solo-filled"
                flat
                hide-details
                single-line
            ></v-text-field>
        </v-card-title>

        <v-divider></v-divider>
        <v-data-table
            :headers="spine_headers"
            :items="spines"
            :items-per-page="8"
            class="elevation-1"
            :search="search"
        >
            <template v-slot:item.print="{ item }">
                <a :href="'/container-terminals/technique/'+item.id+'/print'" target="_blank">
                    <v-icon
                        title="Распечатать корешок"
                        middle
                    >
                        mdi-printer
                    </v-icon>
                </a>
            </template>

            <template v-slot:item.created_at="{ item }">
                {{ convertDateToOurFormat(item.created_at) }}
            </template>
        </v-data-table>
    </v-card>
</template>

<style scoped>

</style>
