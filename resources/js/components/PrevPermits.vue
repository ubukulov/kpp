<template>
    <div class="row">
        <div class="col-md-12 right_content" style="background: burlywood; height: 100vh;">
            <div>
                <h3>Список предварительных пропусков</h3>
            </div>
            <hr>
            <div class="form-group">
                <label>Компьютер</label>
                <select v-model="computer_name" tabindex="10" name="computer_name" class="form-control">
                    <option value="1">КПП №2 (бутик №1)</option>
                    <option value="2">КПП №2 (бутик №2)</option>
                </select>
            </div>
            <v-data-table
                :headers="headers"
                :items="permits"
                item-key="name"
                class="elevation-1"
                :search="search"
                :custom-filter="filterOnlyCapsText"
            >
                <template v-slot:top>
                    <v-text-field
                        v-model="search"
                        label="Поиск"
                        class="mx-4"
                    ></v-text-field>
                </template>
                <template v-slot:item.operation_type="{ item }">
                    <span v-if="item.operation_type == 1">Погрузка</span>
                    <span v-else-if="item.operation_type == 2">Разгрузка</span>
                    <span v-else>Другие действие</span>
                </template>
                <template v-slot:item.print="{ item }">
                    <v-icon
                        middle
                        @click="print_r(item.id)"
                    >
                        mdi-printer
                    </v-icon>
                </template>
            </v-data-table>
        </div>
    </div>
</template>

<script>
    import axios from "axios";

    export default {
        data(){
            return {
                permits: [],
                interval: 0,
                search: '',
                calories: '',
                computer_name: 1
            }
        },
        computed: {
            headers () {
                return [
                    {
                        text: 'ИД',
                        align: 'start',
                        sortable: true,
                        value: 'id',
                    },
                    {
                        text: 'ФИО',
                        align: 'start',
                        sortable: true,
                        value: 'last_name',
                    },
                    {
                        text: '#вод.удос.',
                        value: 'ud_number',
                        filter: value => {
                            if (!this.ud_number) return true

                            return value < parseInt(this.ud_number)
                        },
                    },
                    { text: 'Компания', value: 'company' },
                    { text: 'Вид операции', value: 'operation_type' },
                    { text: 'Телефон', value: 'phone' },
                    { text: 'Гос.номер', value: 'gov_number' },
                    { text: 'Печать', value: 'print' },
                ]
            },
        },

        methods: {
            getPermits(){
                axios.get('/get-prev-permits-for-today')
                    .then(res => {
                        this.permits = res.data;
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            print_r(id){
                axios.get('/command/print/'+id+"/"+this.computer_name)
                .then(res => {
                    console.log(res)
                })
                .catch(err => {
                    console.log(err)
                })
            },
            filterOnlyCapsText (value, search, item) {
                search = search.toUpperCase();
                return value != null &&
                    search != null &&
                    typeof value === 'string' &&
                    value.toString().toLocaleUpperCase().indexOf(search) !== -1
            },
        },
        created(){
            this.getPermits();
            this.interval = setInterval(() => this.getPermits(), 30000);
        }
    }
</script>

<style scoped>

</style>
