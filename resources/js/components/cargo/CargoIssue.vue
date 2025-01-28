<script>
export default {
    data() {
        return {
            agreements: [],
            agreement_id: null,
            files: [],
        }
    }
}
</script>

<template>
<div>
    <v-row>
        <v-col cols="12">
            <v-text-field
                label="Поиск (краткий номер либо Винкод)"
                solo
            ></v-text-field>
        </v-col>

        <v-col cols="4">
            <v-text-field
                label="Номер машины"
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
            >
                <v-icon>mdi-plus-circle-outline</v-icon>&nbsp;Создать
            </v-btn>
        </v-col>
        <v-col cols="12">
            <v-file-input
                accept="image/*"
                label="Прикрепить файл (декларация)"
            ></v-file-input>
        </v-col>
    </v-row>
</div>
</template>

<style scoped>

</style>
