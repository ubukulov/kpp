<template>
    <v-dialog style="z-index: 99999; position: relative; margin-top: 2% !important;" v-model="isModalVisible" persistent>
        <v-card>
            <v-card-title>
                <span class="headline" style="font-size: 20px !important;">Окно: Добавление график</span>
            </v-card-title>
            <v-card-text>
                <v-container>
                    <v-row>
                        <v-col cols="12">
                            <v-select
                                :items="techniques"
                                :hint="`${techniques.id}, ${techniques.name}`"
                                item-value="id"
                                v-model="technique_id"
                                item-text="name"
                                outlined
                                dense
                                label="Выберите технику"
                            ></v-select>
                        </v-col>

                        <v-col cols="12">
                            <v-select
                                :items="crane_users"
                                :hint="`${crane_users.id}, ${crane_users.full_name}`"
                                item-value="id"
                                v-model="crane_user_id"
                                item-text="full_name"
                                outlined
                                dense
                                label="Выберите крановщика"
                            ></v-select>
                        </v-col>

                        <v-col cols="12" class="mb-5">
                            <v-autocomplete
                                :items="slinger_users"
                                label="Выберите стропальщиков"
                                class="form-control"
                                :hint="`${slinger_users.id}, ${slinger_users.full_name}`"
                                :search-input.sync="searchInput"
                                item-value="id"
                                v-model="slinger_ids"
                                item-text="full_name"
                                color="blue-grey lighten-2"
                                multiple
                                box
                                chips
                                hide-details
                                clearable
                                autocomplete="off"
                                :readonly="slinger_ids.length > 1"
                                :menu-props="{closeOnContentClick:true}"
                            >
                                <template v-slot:item="{ item, on, attrs }">
                                    <v-list-item v-on="on" v-bind="attrs" #default="{ active }">
                                        <v-list-item-action>
                                            <v-checkbox :input-value="active"></v-checkbox>
                                        </v-list-item-action>
                                        <v-list-item-content>
                                            <v-list-item-title>
                                                <v-chip color="blue-grey lighten-2"> {{ item.full_name }} </v-chip>
                                            </v-list-item-title>
                                        </v-list-item-content>
                                    </v-list-item>
                                </template>
                            </v-autocomplete>
                        </v-col>
                    </v-row>


                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-btn class="schedule_btn" color="blue darken-1" @click="hideModal">Отменить</v-btn>
                <v-spacer></v-spacer>
                <v-btn class="schedule_btn" color="success darken-1">
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

<script>
import axios from "axios";
import { mapGetters, mapActions } from 'vuex';
export default {
    data() {
        return {
            modal: false,
            techniques: [],
            technique_id: null,
            crane_users: [],
            crane_user_id: null,
            slinger_users: [],
            slinger_ids: []
        }
    },
    computed: {
        ...mapGetters(['isModalVisible'])
    },
    methods: {
        ...mapActions(['hideModal']),
        getCraneUsers(){
            axios.get('/container-controller/get-crane-users')
                .then(res => {
                    this.crane_users = res.data
                })
                .catch(err => {
                    console.log(err)
                })
        },
        getSlingerUsers(){
            axios.get('/container-controller/get-slinger-users')
                .then(res => {
                    this.slinger_users = res.data
                })
                .catch(err => {
                    console.log(err)
                })
        },
        getTechniques(){
            axios.get('/container-controller/get-techniques')
                .then(res => {
                    this.techniques = res.data
                })
                .catch(err => {
                    console.log(err)
                })
        }
    },
    created() {
        this.getTechniques();
        this.getCraneUsers();
        this.getSlingerUsers();
    }
}
</script>

<style scoped>
.schedule_btn {
    color: #fff;
    background-color: cadetblue !important;
}
</style>
