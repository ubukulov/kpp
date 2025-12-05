<script>
import axios from "axios";
import {mapActions, mapGetters} from "vuex";
import Loading from "../system/Loading.vue";
export default {
    name: "WagonCreateTaskModal",
    components: {
        Loading
    },
    data(){
        return {
            wagon_types: [
                {
                    'id': 'submission', 'name': 'Подача'
                },
                {
                    'id': 'cleaning', 'name': 'Уборка'
                },
                {
                    'id': 'shunting', 'name': 'Маневровые'
                }
            ],
            type: 'submission',
            errors: [],
            comments: null,
            file: null,
            showLoading: false
        }
    },
    methods: {
        ...mapActions(['hideWagonModal']),
        createWagonTask(){
            this.errors = [];
            this.showLoading = true;
            let formData = new FormData();
            formData.append('type', this.type);
            formData.append("file", this.file);
            formData.append("comments", this.comments);

            if(this.errors.length === 0) {
                axios.post('/container-terminals/wagon/create-wagon-task', formData)
                    .then(res => {
                        console.log(res);
                        this.showLoading = false;
                        this.$emit('wagon-create-task-modal', {
                            systemText: res.data.message,
                        });
                        this.type = null;
                        this.file = null;
                        this.comments = null;
                        this.hideWagonModal();
                    })
                    .catch(err => {
                        this.showLoading = false;
                        this.errors = err.response.data.errors;
                    })
            }
        }
    },
    computed: {
        ...mapGetters(['isWagonCreateModalVisible'])
    },
}
</script>

<template>
    <v-dialog v-model="isWagonCreateModalVisible" style="z-index: 99999; position: relative; margin-top: 2% !important;" persistent max-width="1000px">
        <v-card>
            <v-card-title>
                <span class="headline" style="font-size: 40px !important;">Вагон: создание заявку</span>
            </v-card-title>
            <v-card-text style="max-height: 700px; overflow-y: auto;">
                <v-container>
                    <v-row>
                        <v-col cols="3">
                            <v-select
                                :items="wagon_types"
                                item-value="id"
                                v-model="type"
                                item-text="name"
                                outlined
                                dense
                                label="Укажите тип"
                            ></v-select>
                        </v-col>

                        <v-col cols="5">
                            <v-file-input
                                label="File input"
                                outlined
                                dense
                                v-model="file"
                            ></v-file-input>
                        </v-col>

                        <v-col cols="4">
                            <a class="btn btn-dark" style="color: #fff;" v-if="type === 'submission'" href="/files/wagon/wagon_submission.xlsx">
                                <v-icon style="color: #fff;">mdi-download</v-icon>&nbsp; Скачать шаблон (подача)
                            </a>
                            <a class="btn btn-dark" style="color: #fff;" v-if="type === 'cleaning'" href="/files/wagon/wagon_cleaning.xlsx">
                                <v-icon style="color: #fff;">mdi-download</v-icon>&nbsp; Скачать шаблон (уборка)
                            </a>
                            <a class="btn btn-dark" style="color: #fff;" v-if="type === 'shunting'" href="/files/wagon/wagon_shunting.xlsx">
                                <v-icon style="color: #fff;">mdi-download</v-icon>&nbsp; Скачать шаблон (маневровые)
                            </a>
                        </v-col>

                        <v-col cols="12">
                            <v-textarea
                                clearable
                                clear-icon="mdi-close-circle"
                                label="Комментарии"
                                v-model="comments"
                            ></v-textarea>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col v-if="errors.length > 0" cols="12">
                            <p style="margin-bottom: 0px !important;">
                                <b>Исправьте ошибки:</b>
                                <ul style="color: #cc0000; padding-left: 15px; list-style: circle; text-align: left;">
                                    <li v-for="error in errors">{{error}}</li>
                                </ul>
                            </p >
                        </v-col>
                    </v-row>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" @click="hideWagonModal">Отменить или закрыть окно</v-btn>
                <v-btn color="success darken-1" @click="createWagonTask()">
                    <v-icon
                        middle
                    >
                        mdi-save
                    </v-icon>
                    &nbsp;Сохранить
                </v-btn>
            </v-card-actions>
        </v-card>

        <Loading :show="showLoading"></Loading>
    </v-dialog>
</template>

<style scoped>

</style>
