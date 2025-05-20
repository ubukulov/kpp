<script>
export default {
    data() {
        return {
            localDialog: this.dialog,
            short_number: null,
            errors: []
        }
    },
    props: [
        'dialog',
        'cargoTask'
    ],
    methods: {
        closeAgreementDialog(){
            this.localDialog = false;
            this.$emit("update:dialog", false);
        },
        completeCargoTask(){
            let formData = new FormData();
            formData.append("cargoTaskId", this.cargoTask.id);
            formData.append("short_number", this.short_number);

            if(!this.short_number){
                this.errors.push('Укажите краткий номер');
            }

            if(this.errors.length === 0){
                axios.post('/container-terminals/cargo/complete/cargo-task', formData)
                    .then(res => {
                        console.log(res);
                        window.location.href = "/container-terminals";
                    })
                    .catch(err => {
                        console.log(err);
                    })
            }
        }
    }
}
</script>

<template>
    <v-dialog style="z-index: 99999; position: relative; margin-top: 10% !important;" v-model="localDialog" persistent max-width="600px">
        <v-card>
            <v-card-title>
                <span class="headline" style="font-size: 40px !important;">Окно: Закрытие заявок</span>
            </v-card-title>
            <v-card-text>
                <v-container>
                    <v-row>

                        <v-col cols="12">
                            <h4>Заявка: {{ (cargoTask.type === 'receive') ? 'IN_'+ cargoTask.id : 'OUT_' + cargoTask.id }}</h4>
                            <v-text-field
                                label="Краткий номер"
                                v-model="short_number"
                                solo
                            ></v-text-field>
                        </v-col>

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
                    </v-row>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" @click="closeAgreementDialog">Отменить</v-btn>
                <v-btn color="success darken-1" @click="completeCargoTask">
                    <v-icon
                        middle
                    >
                        mdi-save
                    </v-icon>
                    &nbsp;Закрыть заявку!
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>

</style>
