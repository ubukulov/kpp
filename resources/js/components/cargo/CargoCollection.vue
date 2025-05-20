<script>
export default {
    data() {
        return {
            cargoNameId: null,
            rows: [
                {
                    cargoNameId: null,
                    quantity: null,
                    weight: null,
                    carNumber: null,
                },
            ],
            oneCar: false,
            vin_code: null,
            car_number: null,
            cars: [],
            codes: [],
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
                quantity: null,
                weight: null,
                carNumber: null,
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
        },
        getCarsForCompany() {
            this.rows = [];
            axios.get(`/container-terminals/cargo/${this.companyId}/get-cars`)
                .then(res => {
                    console.log(res);
                    this.cars = res.data;
                    /*res.data.forEach(code => {
                        console.log(code);
                        this.rows.push({
                            cargoNameId: code.cargoNameId || null,
                            quantity: code.quantity || 1,
                            weight: code.weight || null,
                            carNumber: code.car_number || null
                        });
                    });*/
                })
                .catch(err => {
                    console.log(err);
                })
        },
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
    created() {
        this.getCarsForCompany();
    }
}
</script>

<template>
<div>
    <v-row  class="cargoMain">
        <v-col cols="12">
            <h4>Сведение о грузе</h4>
            <v-autocomplete
                :items="cargoNames"
                :return-object="false"
                :hint="`${cargoNames.id}, ${cargoNames.name}`"
                item-value="id"
                v-model="cargoNameId"
                item-text="name"
                outlined
                dense
                label="Укажите груз (основная)"
            ></v-autocomplete>
        </v-col>
        <v-col cols="5">
            <v-text-field
                label="VINCODE/SERIA"
                v-model="vin_code"
                outlined
                dense
            ></v-text-field>
        </v-col>
        <v-col cols="4">
            <v-autocomplete
                :items="cars"
                label="Номер машины/вагона"
                :hint="`${cars.id}, ${cars.car_number}`"
                v-model="car_number"
                item-value="car_number"
                item-text="car_number"
                outlined
                dense
            ></v-autocomplete>
        </v-col>

        <v-col cols="3">
            <v-checkbox v-model="oneCar" label="Одна машина"></v-checkbox>
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
                dense
                label="Укажите наименование"
            ></v-select>
        </v-col>
        <v-col cols="3">
            <v-text-field
                label="Количество"
                v-model="row.quantity"
                outlined
                dense
            ></v-text-field>
        </v-col>
        <v-col cols="3">
            <v-text-field
                label="Общий вес (в кг)"
                v-model="row.weight"
                outlined
                dense
            ></v-text-field>
        </v-col>
        <v-col cols="6">
            <v-text-field
                v-if="!oneCar"
                label="Номер машины/вагона"
                v-model="row.carNumber"
                outlined
                dense
            ></v-text-field>
        </v-col>
        <v-col cols="6">
            <v-btn v-if="index !== 0" @click="removeRow(index)" color="red" outlined>Удалить позицию</v-btn>
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
