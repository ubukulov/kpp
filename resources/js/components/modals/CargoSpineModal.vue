<template>
    <v-dialog style="z-index: 99999; position: relative; margin-top: 2% !important;" v-model="isModalVisible" persistent max-width="1000px">
        <v-card>
            <v-card-title>
                <span class="headline" style="font-size: 40px !important;">Окно корешок</span>
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
                                v-if="company_id"
                                :items="types"
                                v-model="type"
                                :hint="`${types.value}, ${types.text}`"
                                item-value="value"
                                item-text="text"
                                outlined
                                dense
                                label="Укажите тип"
                            ></v-select>
                        </v-col>

                        <v-col cols="4">
                            <v-select
                                v-if="type === 'receive'"
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

                    <v-row v-if="type === 'ship'">
                        <v-card flat>
                            <v-card-title class="d-flex align-center pe-2">
                                <v-icon icon="mdi-video-input-component"></v-icon> &nbsp;
                                Список винкодов

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
                            ></v-data-table>
                        </v-card>
                    </v-row>



                    <v-row v-if="type === 'receive' && cargoTypeId === 2" v-for="(row, index) in vinCodes" :key="index" class="mb-4 cargoItem">
                        <span>ВИНКОД №{{ index+1 }}</span>

                        <v-col cols="9">
                            <v-text-field
                                label="VINCODE"
                                v-model="row.code"
                                outlined
                                dense
                            ></v-text-field>
                        </v-col>

                        <v-col cols="3">
                            <v-btn v-if="index !== 0" @click="removeRow(index)" color="red" outlined>Убрать винкод</v-btn>
                        </v-col>

                    </v-row>

                    <v-col v-if="type === 'receive' && cargoTypeId === 2" cols="12" class="text-right">
                        <v-btn @click="addRow" color="primary" outlined>Добавить винкод</v-btn>
                    </v-col>

                    <v-row v-if="type" class="mt-3">
                        <v-col cols="4">
                            <v-text-field
                                label="Номер автомашины"
                                v-model="car_number"
                                outlined
                                dense
                            ></v-text-field>
                        </v-col>

                        <v-col cols="5">
                            <v-text-field
                                label="ФИО водителя"
                                v-model="driver_name"
                                dense
                                outlined
                            ></v-text-field>
                        </v-col>

                        <v-col cols="2">
                            <v-text-field dense v-if="type === 'receive' && cargoTypeId === 1" label="Кол-во мест" outlined v-model="countPlaces"></v-text-field>
                            <v-text-field v-if="type === 'receive' && cargoTypeId === 2" class="mt-2" label="Выбрано" disabled :value="vinCodes.length"></v-text-field>
                            <v-text-field v-if="type === 'ship'"  class="mt-2" label="Выбрано" disabled :value="selectedCodes.length"></v-text-field>
                        </v-col>
                    </v-row>

                    <v-col cols="12">
                        <div class="form-group">
                            <p v-if="errors.length" style="margin-bottom: 0px !important;">
                                <b>Пожалуйста исправьте указанные ошибки:</b>
                                <ul style="color: red; padding-left: 15px; list-style: circle; text-align: left;">
                                    <li v-for="error in errors">{{error}}</li>
                                </ul>
                            </p>
                        </div>
                    </v-col>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" @click="hideModal">Отменить или закрыть окно</v-btn>
                <v-btn @click="saveAndPreviewSpine" color="success darken-1">
                    <v-icon
                        middle
                    >
                        mdi-save
                    </v-icon>
                    &nbsp;Сохранить и просмотр
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import axios from "axios";
export default {
    data() {
        return {
            companies: [],
            company_id: null,
            car_number: null,
            driver_name: null,
            types: [
                {
                    'value': 'receive',
                    'text' : 'Размещение'
                },
                {
                    'value': 'ship',
                    'text' : 'Выдача'
                }
            ],
            type: null,
            codes: [],
            code_headers: [
                {
                    text: 'ID',
                    align: 'start',
                    sortable: true,
                    value: 'id',
                },
                { text: 'ВИНКОД', value: 'code'},
            ],
            search: '',
            selectedCodes: [],
            errors: [],
            cargoTypes: [
                {
                    'id': 1, 'name': 'Разобранная'
                },
                {
                    'id': 2, 'name': 'Самоход'
                },
            ],
            cargoTypeId: 1,
            vinCodes: [
                {
                    code: null,
                },
            ],
            countPlaces: null
        }
    },
    computed: {
        ...mapGetters(['isModalVisible'])
    },
    methods: {
        hideModal() {
            this.codes = [];
            this.errors = [];
            this.company_id = null;
            this.type = null;
            this.$store.dispatch('hideModal');
            this.$emit('cargo-spine-modal');
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
        saveAndPreviewSpine(){
            this.errors = [];
            let formData = new FormData();

            if(!this.company_id) {
                this.errors.push('Укажите клиента');
            }

            if(!this.type) {
                this.errors.push('Укажите тип');
            }

            if(this.type === 'receive' && !this.cargoTypeId) {
                this.errors.push('Укажите тип груза');
            }

            if(!this.driver_name) {
                this.errors.push('Укажите ФИО водителя');
            }

            if(!this.car_number) {
                this.errors.push('Укажите номер машины');
            }

            if(this.type === 'receive' && this.cargoTypeId === 1) {

                if(!this.countPlaces) {
                    this.errors.push('Укажите кол-во мест');
                }
            }

            formData.append('company_id', this.company_id);
            formData.append('type', this.type);
            formData.append('driver_name', this.driver_name);
            formData.append('cargoTypeId', this.cargoTypeId);
            formData.append('car_number', this.car_number);

            if(this.type === 'receive' && this.cargoTypeId === 1) {
                formData.append('countPlaces', this.countPlaces);
            }

            if(this.type === 'receive' && this.cargoTypeId === 2) {
                formData.append('vinCodes', JSON.stringify(this.vinCodes));
            }

            if(this.errors.length === 0) {
                axios.post('/container-terminals/cargo/spine', formData)
                    .then(res => {
                        console.log(res);
                        this.company_id = null;
                        this.type = null;
                        this.car_number = null;
                        this.driver_name = null;
                        this.hideModal();
                        let url = `/container-terminals/cargo/${res.data.id}/print`;
                        window.open(url, "_blank");
                    })
                    .catch(err => {
                        console.log(err);
                    })
            }
        },
        addRow() {
            this.vinCodes.push({
                code: null,
            });
        },
        removeRow(index) {
            this.vinCodes.splice(index, 1);
        },
    },
    created() {
        this.getCargoCompanies();
    }
}
</script>

<style scoped>
.v-dialog>.v-card>.v-card__subtitle, .v-dialog>.v-card>.v-card__text {
    padding: 0 14px 10px;
}
.cargoItem {
    border: 1px dashed;
}
</style>
