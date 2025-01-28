<script>
export default {
    data() {
        return {
            cargoNameId: [],
            rows: [
                {
                    cargoNameId: null,
                    quantity: null,
                    weight: null,
                    carNumber: null,
                },
            ],
            oneCar: false
        }
    },
    props: [
        'cargoNames'
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
    },
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
                dense
                label="Укажите груз (основная)"
            ></v-select>
        </v-col>
        <v-col cols="4">
            <v-text-field
                label="VINCODE/SERIA"
                solo
            ></v-text-field>
        </v-col>
        <v-col cols="5">
            <v-text-field
                label="Номер машины"
                solo
            ></v-text-field>
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
                solo
            ></v-text-field>
        </v-col>
        <v-col cols="3">
            <v-text-field
                label="Вес (в кг)"
                v-model="row.weight"
                solo
            ></v-text-field>
        </v-col>
        <v-col cols="6">
            <v-text-field
                v-if="!oneCar"
                label="Номер машины"
                v-model="row.carNumber"
                solo
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
