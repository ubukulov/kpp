<script>
import axios from "axios";

export default {
    data(){
        return {
            name: null,
            full_name: null,
            agreement_file: null,
            errors: [],
            agreements: [],
            agreement_id: null,
            localDialog: this.dialog
        }
    },
    props: [
        'companyId',
        'dialog'
    ],
    methods: {
        storeAgreement(){
            this.errors = [];
            let formData = new FormData();
            formData.append('company_id', this.companyId);
            formData.append('name', this.name);
            formData.append('full_name', this.full_name);
            formData.append('agreement_file', this.agreement_file);

            if(!this.name) {
                this.errors.push('Укажите наименование доверенноста');
            }

            if(!this.full_name) {
                this.errors.push('Укажите ФИО водителя');
            }

            if(!this.agreement_file) {
                this.errors.push('Приложите доверенность');
            }

            if (this.errors.length === 0) {
                this.overlay = true;
                const config = {
                    headers: { 'content-type': 'multipart/form-data' }
                };
                axios.post(`/container-terminals/technique/${this.companyId}/store-agreement`, formData, config)
                    .then(async res => {
                        console.log(res);
                        this.agreement_id = res.data.id;
                        await this.getAgreements();
                        this.overlay = false;
                        this.$emit('cargo-agreement', {
                            agreementId: this.agreement_id,
                            agreements: this.agreements
                        });
                        this.closeAgreementDialog();
                    })
                    .catch(err => {
                        console.log(err);
                        this.overlay = false;
                    })
            }
        },
        closeAgreementDialog(){
            this.localDialog = false;
            this.$emit("update:dialog", false);
        },
        async getAgreements(){
            try {
                const res = await axios.get(`/container-terminals/technique/${this.companyId}/get-agreements`);
                this.agreements = res.data;
            } catch(err) {
                console.log(err);
            }
        },
    },
    watch: {
        dialog(newValue) {
            this.localDialog = newValue
        },
    }
}
</script>

<template>
    <v-dialog style="z-index: 99999; position: relative; margin-top: 10% !important;" v-model="localDialog" persistent max-width="800px">
        <v-card>
            <v-card-title>
                <span class="headline" style="font-size: 40px !important;">Справочник доверенность</span>
            </v-card-title>
            <v-card-text>
                <v-container>
                    <v-row>

                        <v-col cols="12">
                            <v-text-field
                                label="Названия"
                                v-model="name"
                                solo
                            ></v-text-field>

                            <v-text-field
                                label="ФИО водителя"
                                v-model="full_name"
                                solo
                            ></v-text-field>

                            <v-file-input
                                label="Скань доверенноста"
                                v-model="agreement_file"
                                outlined
                                dense
                            ></v-file-input>
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
                <v-btn color="success darken-1" @click="storeAgreement">
                    <v-icon
                        middle
                    >
                        mdi-save
                    </v-icon>
                    &nbsp;Сохранить
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>

</style>
