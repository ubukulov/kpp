<template>
    <v-app>
        <v-main>
            <v-container>
                <template>
                    <v-container>
                        <v-row>
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
                                            v-model="from_date"
                                            label="C"
                                            prepend-icon="event"
                                            readonly
                                            v-bind="attrs"
                                            v-on="on"
                                        ></v-text-field>
                                    </template>
                                    <v-date-picker v-model="from_date" :first-day-of-week="1" locale="ru" @input="menu2 = false"></v-date-picker>
                                </v-menu>
                            </v-col>

                            <v-col md="3">
                                <v-menu
                                    v-model="menu3"
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
                                    <v-date-picker v-model="to_date" :first-day-of-week="1" locale="ru" @input="menu3 = false"></v-date-picker>
                                </v-menu>
                            </v-col>

                            <v-col md="3">
                                <v-autocomplete
                                    :search-input.sync="searchInput"
                                    :hint="`${companies.id}, ${companies.short_ru_name}`"
                                    :items="companies"
                                    v-model="company_id"
                                    item-value="id"
                                    item-text="short_ru_name"
                                    label="-----------------"
                                    required
                                ></v-autocomplete>
                            </v-col>

                            <v-col md="3">
                                <!--<v-btn type="button" @click="getKitchenLogs()" class="primary">Показать</v-btn>-->
                                <v-btn type="button" @click="generateKitchenLogs()" class="primary">Скачать отчет</v-btn>
                                <!--<a style="display:none" v-if="link_excel" :href="href_excel" target="_blank">Выгрузить в Excel</a>-->
                            </v-col>

                            <v-col md="12">
                                <div v-if="progress_circule" class="text-center">
                                    <v-progress-circular
                                        style="width: 100px; height: 100px;"
                                        indeterminate
                                        color="primary"
                                    ></v-progress-circular>
                                </div>
                            </v-col>
                        </v-row>
                    </v-container>
                </template>

                <!--<template>
                    <v-card>
                        <v-card-title>
                            Список пользователей
                            <v-spacer></v-spacer>
                            <v-text-field
                                v-model="search"
                                append-icon="mdi-magnify"
                                label="Поиск"
                                single-line
                                hide-details
                            ></v-text-field>
                            <v-spacer></v-spacer>
                            <v-btn @click="toExcel">
                                <v-icon
                                    middle
                                >
                                    mdi-printer
                                </v-icon>
                                Распечатать
                            </v-btn>
                        </v-card-title>
                        <v-data-table
                            :headers="headers"
                            :items="users"
                            :search="search"
                            id="printTable"
                            disable-pagination
                            hide-default-footer
                        >
                            <template v-slot:item.cnt="{item}">
                                <td v-if="item.cnt > 1" style="background-color: red;">{{item.cnt}}</td>
                                <td v-else>{{item.cnt}}</td>
                            </template>

                            <template v-slot:item.din_type="{item}">
                                <span v-if="item.din_type === 1">Стандарт</span>
                                <span v-else>Булочки</span>
                            </template>
                        </v-data-table>

                    </v-card>
                </template>-->
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
    import axios from 'axios'
    import * as XLSX from 'xlsx'
    export default {
        data(){
            return {
                menu2: false,
                menu3: false,
                link_excel: false,
                href_excel: "",
                from_date: new Date().toISOString().substr(0, 10),
                to_date: new Date().toISOString().substr(0, 10),
                search: '',
                searchInput: '',
                company_id: 0,
                users: [],
                headers: [
                    { text: 'Компания', value: 'company_name' },
                    { text: 'Ф.И.О', value: 'full_name' },
                    { text: 'Должность', value: 'p_name' },
                    { text: 'Тип обеда', value: 'din_type' },
                    { text: 'Талонов', value: 'cnt' },
                ],
                progress_circule: false,
            }
        },
        props: [
            'companies',
            'cashier'
        ],
        methods: {
            getKitchenLogs(){
                let formData = new FormData();
                formData.append('company_id', this.company_id);
                formData.append('cashier_id', this.cashier.id);
                formData.append('from_date', this.from_date);
                formData.append('to_date', this.to_date);
                axios.post('ashana/get-logs', formData)
                    .then(res => {
                        this.users = res.data;
                    })
                    .catch(err => console.log(err))
            },
            toExcel(){
                let file_name = 'stolovaya_' + Date.now() + "( с " + this.from_date + " по " + this.to_date + ")" + '.xlsx';
                if(this.company_id !== 0) {
                    let company = this.companies.find(item => item.id === this.company_id);
                    file_name = company.short_en_name + '_' + Date.now() + "( с " + this.from_date + " по " + this.to_date + ")" + '.xlsx';
                }
                var workbook = XLSX.utils.table_to_book(document.getElementById('printTable'));
                XLSX.writeFile(workbook, file_name);
            },
            generateKitchenLogs(){
                let formData = new FormData();
                formData.append('company_id', this.company_id);
                formData.append('cashier_id', this.cashier.id);
                formData.append('from_date', this.from_date);
                formData.append('to_date', this.to_date);
                axios.post('ashana/generate-logs', formData)
                    .then(res => {
                        window.location.href = res.data;
                        this.progress_circule = false;
                    })
                    .catch(err => console.log(err))
            }
        }
    }
</script>

<style scoped>

</style>
