<script>
import dateformat from "dateformat";
export default {
    data() {
        return {
            techniques: [],
            techniqueIds: [],
            users: [],
            userIds: [],
            square: null,
            count_operations: null,
            areas: [],
            area_id: null,
            ramp: false,
            errors: [],
            file: null,
            previewUrl: null
        }
    },
    props: [
        'cargoTask',
        'cargoStock',
        'cargoLog',
        'user'
    ],
    methods: {
        convertDateToOurFormat(dt){
            return dateformat(dt, 'dd.mm.yyyy HH:MM');
        },
        getTechniques(){
            axios.get('/cargo-controller/get-techniques')
                .then(res => {
                    this.techniques = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
        },
        getUsers(){
            axios.get('/cargo-controller/get-employees')
                .then(res => {
                    this.users = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
        },
        getAreas(){
            axios.get('/cargo-controller/get-areas')
                .then(res => {
                    this.areas = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
        },
        fixOperations(){
            this.errors = [];
            let formData = new FormData();
            formData.append("cargoTaskId", this.cargoTask.id);
            formData.append("cargoStockId", this.cargoStock.id);

            if(this.cargoLog) {
                formData.append('action_type', 'edited');
            }

            if(this.file) {
                formData.append("file", this.file);
            }

            if(this.cargoTask.type === 'receive') {
                if (!this.area_id) {
                    this.errors.push('Укажите площадку');
                }

                formData.append('cargoAreaId', this.area_id);

                if(this.cargoStock.car_number) {
                    // сборная
                    if (this.techniqueIds.length === 0) {
                        this.errors.push('Укажите технику');
                    }

                    if (this.userIds.length === 0) {
                        this.errors.push('Укажите сотрудников');
                    }

                    if (!this.square) {
                        this.errors.push('Укажите кв.м');
                    }

                    if (!this.count_operations) {
                        this.errors.push('Укажите кол-во операции');
                    }

                    formData.append('techniqueIds', this.techniqueIds);
                    formData.append('userIds', this.userIds);
                    formData.append('square', this.square);
                    formData.append('count_operations', this.count_operations);
                } else {
                    // самоход
                    formData.append('ramp', this.ramp);
                }
            } else {
                // выдача
                if(this.cargoStock.cargo_type === 1) {
                    if (this.techniqueIds.length === 0) {
                        this.errors.push('Укажите технику');
                    }

                    if (this.userIds.length === 0) {
                        this.errors.push('Укажите сотрудников');
                    }

                    if (!this.count_operations) {
                        this.errors.push('Укажите кол-во операции');
                    }

                    formData.append('techniqueIds', this.techniqueIds);
                    formData.append('userIds', this.userIds);
                    formData.append('count_operations', this.count_operations);
                } else {
                    formData.append('ramp', this.ramp);
                }
            }

            if (this.errors.length === 0) {
                axios.post('/cargo-controller/cargo/fix-operations', formData, {
                    headers: { "Content-Type": "multipart/form-data" }
                })
                    .then(res => {
                        console.log(res)
                        window.location.href = `/cargo-controller/cargo-task/${this.cargoTask.id}/show`;
                    })
                    .catch(err => {
                        console.log(err)
                    })
            }
        },
        handlePhoto(event) {
            const file = event; // В Vuetify v-file-input передает file напрямую

            if (file) {
                console.log("Файл загружен:", file);
                this.file = file;
                this.previewUrl = window.URL.createObjectURL(file);
            }
        }
    },
    created() {
        this.getTechniques();
        this.getUsers();
        this.getAreas();
        if(this.cargoLog) {
            this.techniqueIds = this.cargoLog.technique_ids
                ? this.cargoLog.technique_ids.split(',').map(Number)
                : [];

            this.userIds = this.cargoLog.user_ids
                ? this.cargoLog.user_ids.split(',').map(Number)
                : [];
            this.area_id = this.cargoStock.cargo_area_id;
            this.square = this.cargoLog.square;
            this.count_operations = this.cargoLog.count_operations;
            this.previewUrl = this.cargoStock.image;
        }

    }
}
</script>

<template>
    <v-app>
        <v-main>
            <v-container>
                <v-row>
                    <v-col cols="12" class="card-flex mb-1">
                        <div>
                            <a class="btn btn-outline-primary" :href="`/cargo-controller/cargo-task/${cargoTask.id}/show`">Назад</a>
                        </div>

                        <div>
                            <a class="btn btn-outline-primary" href="/logout">Выйти</a>
                        </div>
                    </v-col>
                    <v-col cols="12" style="border: 1px dashed;">
                        <div class="card-flex">
                            <div>
                                <p>{{ user.full_name }}</p>
                            </div>

                            <div>

                            </div>
                        </div>

                        <div class="card-flex">
                            <div>
                                <p v-if="cargoTask.type === 'receive'">Заяка: №IN_{{ cargoTask.id }}</p>
                                <p v-else>Заяка: №OUT_{{ cargoTask.id }}</p>
                            </div>

                            <div>
                                <p v-if="cargoTask.type === 'receive'">Тип: ЗАВОЗ</p>
                                <p v-else>Тип: ВЫВОЗ</p>
                            </div>
                        </div>
                        <div>
                            Позиция: {{ cargoStock.cargo.name }}
                        </div>
                    </v-col>

                </v-row>

                <v-row v-if="cargoStock.cargo_type === 1">
                    <v-col
                        cols="12"
                    >
                        <v-autocomplete
                            :items="techniques"
                            :return-object="false"
                            :hint="`${techniques.id}, ${techniques.name}`"
                            item-value="id"
                            v-model="techniqueIds"
                            item-text="name"
                            multiple
                            solo
                            variant="solo-filled"
                            label="Укажите технику"
                        ></v-autocomplete>
                    </v-col>

                    <v-col cols="12">
                        <v-autocomplete
                            :items="users"
                            :return-object="false"
                            :hint="`${users.id}, ${users.full_name}`"
                            item-value="id"
                            v-model="userIds"
                            item-text="full_name"
                            multiple
                            solo
                            variant="solo-filled"
                            label="Укажите сотрудников"
                        ></v-autocomplete>
                    </v-col>

                    <v-col v-if="cargoTask.type === 'receive'" cols="12">
                        <v-select
                            :items="areas"
                            :return-object="false"
                            :hint="`${areas.id}, ${areas.name}`"
                            item-value="id"
                            v-model="area_id"
                            item-text="name"
                            outlined
                            dense
                            label="Укажите площадку"
                        ></v-select>
                    </v-col>

                    <v-col v-if="cargoTask.type === 'receive'" cols="6">
                        <v-text-field
                            label="Укажите кв.м"
                            v-model="square"
                            solo
                        ></v-text-field>
                    </v-col>

                    <v-col cols="6">
                        <v-text-field
                            label="Кол-во операции"
                            v-model="count_operations"
                            solo
                        ></v-text-field>
                    </v-col>
                </v-row>

                <v-row v-else>
                    <v-col cols="12">
                        <v-switch label="Пандус" v-model="ramp"></v-switch>
                    </v-col>

                    <v-col v-if="cargoTask.type === 'receive'" cols="12">
                        <v-select
                            :items="areas"
                            :return-object="false"
                            :hint="`${areas.id}, ${areas.name}`"
                            item-value="id"
                            v-model="area_id"
                            item-text="name"
                            outlined
                            dense
                            label="Укажите площадку"
                        ></v-select>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="12">
                        <v-file-input
                            label="Сделать фото"
                            accept="image/*"
                            capture="environment"
                            @change="handlePhoto"
                        ></v-file-input>

                        <img v-if="previewUrl !== null" :src="previewUrl" style="max-width: 100%; max-height: 300px;">
                    </v-col>
                </v-row>

                <v-row>
                    <v-col v-if="errors.length" cols="12">
                        <p style="margin-bottom: 0px !important;">
                            <b>Исправьте ошибки:</b>
                            <ul style="color: #cc0000; padding-left: 15px; list-style: circle; text-align: left;">
                                <li v-for="error in errors">{{error}}</li>
                            </ul>
                        </p >
                    </v-col>

                    <v-col cols="12">
                        <button @click="fixOperations" type="button" class="btn btn-outline-primary w100">Сохранить</button>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.w100 {
    width: 100%;
}
.card-flex {
    display: flex;
    justify-content: space-between;
}
.card-btn {
    background: #28A745;
    color: #fff;
    width: 100%;
    padding: 10px;
    font-size: 16px !important;
}
</style>
