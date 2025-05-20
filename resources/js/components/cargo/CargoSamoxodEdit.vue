<script>
import axios from "axios";

export default {
    data() {
        return {
            rows: [
                {
                    cargoStockId: null,
                    cargoNameId: null,
                    quantity: 1,
                    weight: null,
                    vin_code: null,
                    status: null
                },
            ],
        }
    },
    props: [
        'cargoNames',
        'cargoTask'
    ],
    methods: {
        addRow() {
            this.rows.push({
                cargoStockId: null,
                cargoNameId: null,
                quantity: 1,
                weight: null,
                vin_code: null,
                status: 'incoming'
            });
        },
        removeRow(index) {
            this.rows.splice(index, 1);
        },
        sendData() {
            this.$emit('cargo-receive-samoxod', {
                rows: this.rows
            });
        }
    },
    watch: {
        rows: {
            handler: 'sendData',
            deep: true
        }
    },
    created() {
        let stocks = this.cargoTask.stocks;
        if(stocks.length > 0) this.rows = [];
        for (let i = 0; i <= stocks.length; i++) {
            this.rows.push({
                cargoStockId: stocks[i].id,
                cargoNameId: stocks[i].cargo_id,
                quantity: stocks[i].quantity,
                weight: stocks[i].weight,
                vin_code: stocks[i].vin_code,
                status: stocks[i].status,
            });
        }
    }
}
</script>

<template>
    <div>
        <v-row v-for="(row, index) in rows" :key="index" class="mb-4 cargoItem">
            <span>Позиция №{{ index+1 }}</span>
            <v-col cols="6">
                <v-select
                    :items="cargoNames"
                    :return-object="false"
                    :hint="`${cargoNames.id}, ${cargoNames.name}`"
                    item-value="id"
                    v-model="row.cargoNameId"
                    item-text="name"
                    outlined
                    :disabled="row.status !== 'incoming'"
                    dense
                    label="Укажите наименование"
                ></v-select>
            </v-col>
            <v-col cols="3">
                <v-text-field
                    label="Количество"
                    v-model="row.quantity"
                    disabled
                    solo
                    :disabled="row.status !== 'incoming'"
                ></v-text-field>
            </v-col>
            <v-col cols="3">
                <v-text-field
                    label="Общий вес (в кг)"
                    v-model="row.weight"
                    solo
                    :disabled="row.status !== 'incoming'"
                ></v-text-field>
            </v-col>
            <v-col cols="6">
                <v-text-field
                    label="VINCODE / SERIA"
                    v-model="row.vin_code"
                    solo
                    :disabled="row.status !== 'incoming'"
                ></v-text-field>
            </v-col>
            <v-col cols="6">
                <v-btn v-if="!row.cargoNameId" :disabled="row.status === 'received'" @click="removeRow(index)" color="red" outlined>Удалить позицию</v-btn>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="12" class="text-right">
                <v-btn @click="addRow" color="primary" outlined>Добавить позицию</v-btn>
            </v-col>
        </v-row>
    </div>
</template>

<style scoped>
.cargoItem {
    border: 1px dashed;
}
</style>
