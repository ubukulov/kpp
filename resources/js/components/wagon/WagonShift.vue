<script>
import axios from "axios";

export default {
    name: "WagonShift",
    data() {
        return {
            users: [],
            userIds: []
        }
    },
    methods: {
        getEmployees() {
            axios.get('/container-terminals/wagon/get-wagon-employees')
                .then(res => {
                    console.log(res.data)
                    this.users = res.data;
                })
                .catch(err => {
                    console.log(err);
                })
        },
        formatFullName(item){
            return `${item.full_name} (${item.position_name})`
        },
        limitSelection() {
            if (this.userIds.length > 3) {
                this.userIds = this.userIds.slice(0, 3);
                this.$nextTick(() => {
                    this.$emit('notify', 'Можно выбрать максимум 3 сотрудников');
                });
            }
        },
        updateWagonShifts(){
            let formData = new FormData();
            formData.append('userIds', JSON.stringify(this.userIds));
            axios.post('/container-terminals/wagon/update-wagon-shifts', formData)
                .then(res => {
                    console.log(res);
                    this.$store.dispatch('saveShifts', res.data.data)
                })
                .catch(err => {
                    console.log(err);
                })
        }
    },
    created() {
        this.getEmployees();

        if (this.$store.getters.currentShifts?.length) {
            this.userIds = this.$store.getters.currentShifts;
        }
    }
}
</script>

<template>
<div>
    <v-row>
        <v-col cols="12">
            <h1>Вкладка Смены</h1>
            <h5>Укажите с кем работаете сейчас</h5>
        </v-col>

        <v-col cols="12">
            <v-autocomplete
                :items="users"
                item-value="id"
                v-model="userIds"
                :item-text="formatFullName"
                label="Выберите сотрудников"
                autocomplete
                outlined
                dense
                style="margin-top: 10px !important;"
                multiple
                @change="limitSelection"
            ></v-autocomplete>
        </v-col>

        <v-col cols="12">
            <v-btn @click="updateWagonShifts" color="green" outlined>Сохранить смену</v-btn>
        </v-col>
    </v-row>
</div>
</template>

<style scoped>

</style>
