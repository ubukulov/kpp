<script>
import axios from "axios";

export default {
    data() {
        return {
            cargoTechniques: [],
            rows: [
                {
                    cargoNameId: null,
                    quantity: 1,
                    weight: null,
                    vin_code: null,
                    spineId: null
                },
            ],
            codes: []
        }
    },
    props: [
        'cargoNames',
        'companyId'
    ],
    methods: {
        addRow() {
            this.rows.push({
                cargoNameId: null,
                quantity: 1,
                weight: null,
                vin_code: null,
            });
        },
        removeRow(index) {
            this.rows.splice(index, 1);
        },
        sendData() {
            this.$emit('cargo-receive-samoxod', {
                rows: this.rows
            });
        },
        getCodesForCar(){
            this.rows = [];
            axios.get(`/container-terminals/cargo/${this.companyId}/get-codes`)
                .then(res => {
                    console.log(res);
                    res.data.forEach(code => {
                        this.rows.push({
                            cargoNameId: null,
                            quantity: 1,
                            weight: null,
                            vin_code: code.vin_code,
                            spineId: code.spine_id
                        });
                    });
                })
                .catch(err => {
                    console.log(err);
                })
        }
    },
    watch: {
        rows: {
            handler: 'sendData',
            deep: true
        }
    },
    created() {
        this.cargoTechniques = this.cargoNames.filter(t => t.type === 2)
        this.getCodesForCar();
    }
}
</script>

<template>
    <div>
        <v-row v-for="(row, index) in rows" :key="index" class="mb-4 cargoItem">
            <span>Позиция №{{ index+1 }}</span>
            <v-col cols="6">
                <v-select
                    :items="cargoTechniques"
                    :return-object="false"
                    :hint="`${cargoTechniques.id}, ${cargoTechniques.name}`"
                    item-value="id"
                    v-model="row.cargoNameId"
                    item-text="name"
                    outlined
                    dense
                    label="Укажите технику"
                ></v-select>
            </v-col>
            <v-col cols="3">
                <v-text-field
                    label="Количество"
                    v-model="row.quantity"
                    disabled
                    solo
                ></v-text-field>
            </v-col>
            <v-col cols="3">
                <v-text-field
                    label="Общий вес (в кг)"
                    v-model="row.weight"
                    solo
                ></v-text-field>
            </v-col>
            <v-col cols="6">
                <v-text-field
                    label="VINCODE / SERIA"
                    v-model="row.vin_code"
                    solo
                ></v-text-field>
            </v-col>
            <v-col cols="6">
                <v-btn v-if="index !== 0 && !row.spineId" @click="removeRow(index)" color="red" outlined>Убрать позицию</v-btn>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="12" class="text-right">
                <v-btn v-if="rows.length > 0" @click="addRow" color="primary" outlined>Добавить позицию</v-btn>
            </v-col>
        </v-row>
    </div>
</template>

<style scoped>
.cargoItem {
    border: 1px dashed;
}
</style>
