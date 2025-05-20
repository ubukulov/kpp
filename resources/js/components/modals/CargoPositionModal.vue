<script>
import axios from "axios";
import CargoCollectionEdit from "../cargo/CargoCollectionEdit.vue";
import CargoSamoxodEdit from "../cargo/CargoSamoxodEdit.vue";
import CargoIssue from "../cargo/CargoIssue.vue";
export default {
    components: {
        CargoCollectionEdit,
        CargoSamoxodEdit,
        CargoIssue,
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
                    'id': 1, 'name': 'Разобранная'
                },
                {
                    'id': 2, 'name': 'Самоход'
                },
            ],
            cargoTypeId: null,
            cargoNames: [],
            cargoCollectionData: {},
            errors: [],
            localDialog: this.dialog
        }
    },
    props: [
        'cargoTask',
        'dialog'
    ],
    methods: {
        closeAgreementDialog(){
            this.localDialog = false;
            this.$emit("update:dialog", false);
        },
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
        cargoReceiveCollection(data){
            this.cargoCollectionData = data;
        },
        cargoReceiveSamoxod(data){
            this.cargoCollectionData = data;
        },
        updateCargo(){
            this.errors = [];
            let formData = new FormData();
            const config = {
                headers: { 'content-type': 'multipart/form-data' }
            };

            if (!this.company_id) {
                this.errors.push('Укажите клиента');
            }

            if (!this.orderTypeId) {
                this.errors.push('Укажите тип');
            }

            formData.append('company_id', this.company_id);
            formData.append('orderTypeId', this.orderTypeId);
            if(this.orderTypeId === 1) {
                if (!this.cargoTypeId) {
                    this.errors.push('Укажите тип груза');
                }
                formData.append('cargoTypeId', this.cargoTypeId);
                formData.append('cargoData', JSON.stringify(this.cargoCollectionData))
            } else {
                const dataToSend = { ...this.cargoCollectionData };
                delete dataToSend.file;
                formData.append('cargoData', JSON.stringify(dataToSend))
                formData.append('file', this.cargoCollectionData.file)
            }

            if(this.errors.length === 0) {
                axios.post(`/container-terminals/cargo/${this.cargoTask.id}/update`, formData, config)
                    .then(res => {
                        //console.log(res)
                        /*this.company_id = null;
                        this.orderTypeId = null;
                        this.cargoTypeId = null;
                        this.cargoCollectionData = {};
                        this.hideCargoModal();
                        this.callParentMethod();*/
                    })
                    .catch(err => {
                        console.log(err)
                    })
            }
        },
        callParentMethod() {
            this.$emit('call-parent-method');
        },
        cargoIssue(data){
            this.cargoCollectionData = data;
        }
    },
    created() {
        this.getCargoCompanies();
        this.getCargoNames();
        this.company_id = this.cargoTask.company_id;
        this.orderTypeId = (this.cargoTask.type === 'ship') ? 2 : 1;
        this.cargoTypeId = (this.cargoTask.cargo === 'disassembled') ? 1 : 2;
    }
}
</script>

<template>
    <v-dialog style="z-index: 99999; position: relative; margin-top: 2% !important;" v-model="localDialog" persistent max-width="1000px">
        <v-card>
            <v-card-title>
                <span class="headline" style="font-size: 40px !important;">Груз: Редактирование</span>
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
                                disabled
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
                                disabled
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
                                disabled
                                label="Тип груза"
                            ></v-select>
                        </v-col>
                    </v-row>

                    <CargoCollectionEdit
                        @cargo-receive-collection="cargoReceiveCollection"
                        v-if="cargoTypeId === 1 && orderTypeId === 1"
                        :cargoNames="cargoNames"
                        :cargoTask="cargoTask"
                    ></CargoCollectionEdit>

                    <CargoSamoxodEdit
                        @cargo-receive-samoxod="cargoReceiveSamoxod"
                        v-if="cargoTypeId === 2 && orderTypeId === 1"
                        :cargoNames="cargoNames"
                        :cargoTask="cargoTask"
                    ></CargoSamoxodEdit>

                    <CargoIssue
                        v-if="orderTypeId === 2"
                        @cargo-issue="cargoIssue"
                        :companyId="company_id"
                    ></CargoIssue>

                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" @click="closeAgreementDialog">Отменить или закрыть окно</v-btn>
                <v-btn :disabled="Object.keys(cargoCollectionData).length === 0" color="success darken-1" @click="updateCargo">
                    <v-icon
                        middle
                    >
                        mdi-save
                    </v-icon>
                    &nbsp;Сохранить
                </v-btn>
            </v-card-actions>
            <v-container>
                <v-row>
                    <v-col v-if="errors.length" cols="12">
                        <p style="margin-bottom: 0px !important;">
                            <b>Исправьте ошибки:</b>
                            <ul style="color: #cc0000; padding-left: 15px; list-style: circle; text-align: left;">
                                <li v-for="error in errors">{{error}}</li>
                            </ul>
                        </p >
                    </v-col>
                </v-row>
            </v-container>
        </v-card>
    </v-dialog>
</template>

<style scoped>

</style>
