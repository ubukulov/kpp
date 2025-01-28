<script>
import {mapActions, mapGetters} from "vuex";
import axios from "axios";
import CargoCollection from "../cargo/CargoCollection.vue";
import CargoSamoxod from "../cargo/CargoSamoxod.vue";
import CargoIssue from "../cargo/CargoIssue.vue";
export default {
    components: {
        CargoCollection,
        CargoSamoxod,
        CargoIssue
    },
    data() {
        return {
            companies: [],
            company_id: null,
            orderTypes: [
                {
                    'id': 1, 'name': 'Прием'
                },
                {
                    'id': 2, 'name': 'Выдача'
                },
            ],
            orderTypeId: null,
            cargoTypes: [
                {
                    'id': 1, 'name': 'Сборная'
                },
                {
                    'id': 2, 'name': 'Самоход'
                },
            ],
            cargoTypeId: null,
            cargoNames: []
        }
    },
    computed: {
        ...mapGetters(['isCargoModalVisible'])
    },
    methods: {
        ...mapActions(['hideCargoModal']),
        getCargoCompanies(){
            axios.get('/container-terminals/cargo/get-cargo-companies')
                .then(res => {
                    console.log(res);
                    this.companies = res.data;
                })
                .catch(err => {
                    console.log(err);
                })
        },
        getCargoNames(){
            axios.get('/container-terminals/cargo/get-cargo-names')
                .then(res => {
                    console.log(res);
                    this.cargoNames = res.data;
                })
                .catch(err => {
                    console.log(err);
                })
        },
    },
    created() {
        this.getCargoCompanies();
        this.getCargoNames();
    }
}
</script>

<template>
    <v-dialog style="z-index: 99999; position: relative; margin-top: 2% !important;" v-model="isCargoModalVisible" persistent max-width="1000px">
        <v-card>
            <v-card-title>
                <span class="headline" style="font-size: 40px !important;">Груз: Окно добавление</span>
            </v-card-title>
            <v-card-text style="max-height: 700px; overflow-y: auto;">
                <v-container>
                    <v-row>
                        <v-col cols="4">
                            <v-select
                                :items="companies"
                                :hint="`${companies.id}, ${companies.short_en_name}`"
                                item-value="id"
                                v-model="company_id"
                                item-text="short_en_name"
                                outlined
                                dense
                                label="Выберите клиента"
                            ></v-select>
                        </v-col>

                        <v-col cols="4">
                            <v-select
                                :items="orderTypes"
                                :hint="`${orderTypes.id}, ${orderTypes.name}`"
                                item-value="id"
                                v-model="orderTypeId"
                                item-text="name"
                                outlined
                                dense
                                label="Тип заявки"
                            ></v-select>
                        </v-col>

                        <v-col cols="4">
                            <v-select
                                v-if="orderTypeId === 1"
                                :items="cargoTypes"
                                :hint="`${cargoTypes.id}, ${cargoTypes.name}`"
                                item-value="id"
                                v-model="cargoTypeId"
                                item-text="name"
                                outlined
                                dense
                                label="Тип груза"
                            ></v-select>
                        </v-col>
                    </v-row>

                    <CargoCollection v-if="cargoTypeId === 1 && orderTypeId === 1" :cargoNames="cargoNames"></CargoCollection>
                    <CargoSamoxod v-if="cargoTypeId === 2 && orderTypeId === 1" :cargoNames="cargoNames"></CargoSamoxod>
                    <CargoIssue v-if="orderTypeId === 2"></CargoIssue>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" @click="hideCargoModal">Отменить или закрыть окно</v-btn>
                <v-btn color="success darken-1">
                    <v-icon
                        middle
                    >
                        mdi-save
                    </v-icon>
                    &nbsp;Сохранить
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>

</style>
