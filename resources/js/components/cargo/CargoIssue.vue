<script>
import axios from "axios";
import AgreementModal from "../modals/AgreementModal.vue";
export default {
    components: {
        AgreementModal
    },
    data() {
        return {
            agreements: [],
            agreement_id: null,
            file: null,
            search: '',
            selectedCodes: [],
            codes: [],
            code_headers: [
                {
                    text: 'ID',
                    align: 'start',
                    sortable: true,
                    value: 'id',
                },
                { text: 'Наименование', value: 'cargo_name'},
                { text: 'ВИНКОД', value: 'vin_code'},
                { text: 'Остаток', value: 'quantity'},
                { text: 'Кол-во', value: 'qty'},
            ],
            gov_number: null,
            driver_name: null,
            agreementDialog: false,
            quantities: []
        }
    },
    props: [
        'companyId'
    ],
    methods: {
        getCargoStocksForShip(){
            axios.get(`/container-terminals/cargo/${this.companyId}/get-cargo-stocks-for-shipment`)
                .then(res => {
                    console.log(res);
                    this.codes = res.data;
                })
                .catch(err => {
                    console.log(err);
                })
        },
        handleFileChange(file) {
            this.file = file;
        },
        getAgreements(){
            axios.get(`/container-terminals/technique/${this.companyId}/get-agreements`)
                .then(res => {
                    console.log(res);
                    this.agreements = res.data;
                })
                .catch(err => {
                    console.log(err);
                })
        },
        cargoAgreement(data){
            this.agreements = data.agreements;
            this.agreement_id = data.agreementId;
        },
        sendData(){
            this.$emit('cargo-issue', {
                agreement_id: this.agreement_id,
                selectedCodes: this.selectedCodes,
                quantities: this.quantities,
                gov_number: this.gov_number,
                driver_name: this.driver_name,
                file: this.file,
            });
        },
        checkMax(item) {
            if (item.value > item.quantity) {
                item.value = item.quantity;

                this.quantities.push({
                    vin_code: item.vin_code,
                    quantity: item.value,
                });
            } else {
                this.quantities.push({
                    vin_code: item.vin_code,
                    quantity: item.value,
                });
            }
        }
    },
    watch: {
        agreement_id: 'sendData',
        selectedCodes: 'sendData',
        gov_number: 'sendData',
        driver_name: 'sendData',
        quantities: 'sendData',
        file: {
            handler: 'sendData',
            deep: false, // Не нужно отслеживать вложенные свойства
            immediate: false, // Не запускать сразу при загрузке компонента
        }
    },
    created() {
        this.getCargoStocksForShip();
        this.getAgreements();
    }
}
</script>

<template>
    <div>
        <v-row>
            <v-col cols="12">

                <AgreementModal
                    v-if="agreementDialog"
                    :company-id="companyId"
                    :dialog="agreementDialog"
                    @cargo-agreement="cargoAgreement"
                    @update:dialog="agreementDialog = $event"
                ></AgreementModal>

                <v-card flat>
                    <v-card-title class="d-flex align-center pe-2">
                        <v-icon icon="mdi-video-input-component"></v-icon> &nbsp;
                        Список грузов

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
                        :headers="code_headers"
                        :items="codes"
                        :items-per-page="8"
                        class="elevation-1"
                        show-select
                        :search="search"
                        v-model="selectedCodes"
                    >
                        <template v-slot:item.qty="{ item }">
                            <input class="edit-input" type="number" min="1" :max="item.quantity" v-model.number="item.value" @change="checkMax(item)">
                        </template>
                    </v-data-table>
                </v-card>
            </v-col>

            <v-col cols="6">
                <v-text-field
                    label="ФИО водителя"
                    v-model="driver_name"
                    solo
                ></v-text-field>
            </v-col>

            <v-col cols="6">
                <v-text-field
                    label="Номер машины"
                    v-model="gov_number"
                    solo
                ></v-text-field>
            </v-col>

            <v-col cols="6">
                <v-select
                    :items="agreements"
                    class="form-control"
                    :hint="`${agreements.id}, ${agreements.name}`"
                    item-value="id"
                    v-model="agreement_id"
                    item-text="name"
                ></v-select>
                <v-btn
                    class="ma-2"
                    outlined
                    color="indigo"
                    @click="agreementDialog = true"
                >
                    <v-icon>mdi-plus-circle-outline</v-icon>&nbsp;Создать
                </v-btn>
            </v-col>
            <v-col cols="6">
                <v-file-input
                    accept=".pdf, .doc, .docx"
                    label="Прикрепить файл (декларация)"
                    v-model="file"
                    @change="handleFileChange"
                ></v-file-input>
            </v-col>
        </v-row>
    </div>
</template>

<style scoped>
.edit-input {
    width: 40px;
    border: 1px solid #ccc;
    border-radius: 5px;
    text-align: center;
    color: rgba(0, 0, 0, .87);
    font-size: 14px !important;
}
.edit-input::-webkit-inner-spin-button,
.edit-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.edit-input {
    -moz-appearance: textfield;
}
</style>
