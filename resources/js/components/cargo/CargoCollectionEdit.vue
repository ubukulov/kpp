<script>
export default {
    data() {
        return {
            cargoNameId: null,
            rows: [
                {
                    cargoStockId: null,
                    cargoNameId: null,
                    quantity: null,
                    weight: null,
                    carNumber: null,
                    status: null
                },
            ],
            oneCar: false,
            vin_code: null,
            car_number: null
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
                quantity: null,
                weight: null,
                carNumber: null,
                status: 'incoming'
            });
        },
        removeRow(index) {
            this.rows.splice(index, 1);
        },
        sendData() {
            this.$emit('cargo-receive-collection', {
                cargoNameId: this.cargoNameId,
                vin_code: this.vin_code,
                car_number: this.car_number,
                oneCar: this.oneCar,
                rows: this.rows
            });
        }
    },
    watch: {
        cargoNameId: 'sendData',
        vin_code: 'sendData',
        car_number: 'sendData',
        oneCar: 'sendData',
        rows: {
            handler: 'sendData',
            deep: true
        }
    },
    created(){
        console.log('ss', this.cargoTask);
        this.cargoNameId = this.cargoTask.stocks[0].cargo_id;
        this.vin_code = this.cargoTask.stocks[0].vin_code;
        this.car_number = this.cargoTask.stocks[0].car_number;

        let stocks = this.cargoTask.stocks;
        if(stocks.length > 0) this.rows = [];
        for (let i = 1; i <= stocks.length; i++) {
            this.rows.push({
                cargoStockId: stocks[i].id,
                cargoNameId: stocks[i].cargo_id,
                quantity: stocks[i].quantity,
                weight: stocks[i].weight,
                carNumber: stocks[i].car_number,
                status: stocks[i].status,
            });
        }
        console.log("ssa", stocks)
    }
}
</script>

<template>
    <div>
        <v-row class="cargoMain">
            <v-col cols="12">
                <v-select
                    :items="cargoNames"
                    :return-object="false"
                    :hint="`${cargoNames.id}, ${cargoNames.name}`"
                    item-value="id"
                    v-model="cargoNameId"
                    item-text="name"
                    outlined
                    disabled
                    dense
                    label="Укажите груз (основная)"
                ></v-select>
            </v-col>
            <v-col cols="5">
                <v-text-field
                    label="VINCODE/SERIA"
                    v-model="vin_code"
                    solo
                    disabled
                ></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field
                    label="Номер машины/вагона"
                    v-model="car_number"
                    solo
                    disabled
                ></v-text-field>
            </v-col>

            <v-col cols="3">
<!--                <v-checkbox v-model="oneCar" label="Одна машина"></v-checkbox>-->
            </v-col>
        </v-row>

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
                    v-if="!oneCar"
                    label="Номер машины/вагона"
                    v-model="row.carNumber"
                    solo
                    :disabled="row.status !== 'incoming'"
                ></v-text-field>
            </v-col>
            <v-col cols="6">
                <v-btn v-if="!row.cargoNameId" @click="removeRow(index)" color="red" outlined>Удалить позицию</v-btn>
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
.cargoMain {
    border: 2px solid green;
    margin-bottom: 20px;
}
</style>
