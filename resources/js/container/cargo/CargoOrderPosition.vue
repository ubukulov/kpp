<script>
import dateformat from "dateformat";
export default {
    data() {
        return {
            techniques: [],
            users: [],
            square: null,
            areas: [],
            area_id: null,
            errors: [],
            file: null,
            previewUrl: null,
            services: []
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
            axios.get('/cabinet/webcont/cargo-controller/get-techniques')
                .then(res => {
                    this.techniques = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
        },
        getUsers(){
            axios.get('/cabinet/webcont/cargo-controller/get-employees')
                .then(res => {
                    this.users = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
        },
        getAreas(){
            axios.get('/cabinet/webcont/cargo-controller/get-areas')
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

                if (!this.square) {
                    this.errors.push('Укажите кв.м');
                }

                formData.append('cargoAreaId', this.area_id);
                formData.append('square', this.square);
                formData.append('services', JSON.stringify(this.services));
            } else {
                // выдача
                formData.append('services', JSON.stringify(this.services));
            }

            if(this.services.length === 0) {
                this.errors.push('Укажите услуги');
            } else {
                this.services.forEach((row, i) => {
                    const position = i + 1;

                    if(!row.techniqueId) {
                        this.errors.push(`Укажите технику. Позиция №${position}`);
                    }

                    if(this.getService(row.techniqueId)?.slug !== 'ramp' && this.getService(row.techniqueId)?.slug !== 'self' && this.getService(row.techniqueId)?.slug !== 'without_unloading') {
                        if(row.userId.length === 0) {
                            this.errors.push(`Укажите сотрудника. Позиция №${position}`);
                        }
                    }

                    if(!row.count_operations && this.getService(row.techniqueId)?.slug !== 'ramp' && this.getService(row.techniqueId)?.slug !== 'self' && this.getService(row.techniqueId)?.slug !== 'without_unloading') {
                        this.errors.push(`Укажите кол-во операции. Позиция №${position}`);
                    }
                })
            }


            if (this.errors.length === 0) {
                axios.post('/cabinet/webcont/cargo-controller/cargo/fix-operations', formData, {
                    headers: { "Content-Type": "multipart/form-data" }
                })
                    .then(res => {
                        console.log(res)
                        //window.location.href = `/cabinet/webcont/cargo-controller/cargo-task/${this.cargoTask.id}/show`;
                        window.location.href = `/cabinet/webcont/cargo-controller`;
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
        },
        addRow() {
            this.services.push({
                techniqueId: null,
                userId: [],
                count_operations: null,
            });
        },
        removeRow(index) {
            this.services.splice(index, 1);
        },
        getService(techniqueId){
            return this.techniques.find(item => item.id === techniqueId);
        }
    },
    watch: {
        services: {
            handler(newServices) {
                newServices.forEach((service) => {
                    if (Array.isArray(service.userId) && service.userId.length > 3) {
                        service.userId = service.userId.slice(0, 3);
                    }
                });
            },
            deep: true
        }
    },
    created() {
        this.getTechniques();
        this.getUsers();
        this.getAreas();
        /*if(this.cargoLog) {
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
        }*/

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
                            <a class="btn btn-outline-primary" :href="`/cabinet/webcont/cargo-controller/cargo-task/${cargoTask.id}/show`">Назад</a>
                        </div>

                        <div>

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

                <v-row style="border-bottom: 1px dashed #0c525d;" v-for="(item, index) in services" :key="index">
                    <span class="service-span">Услуга №{{index+1}}. {{ getService(item.techniqueId)?.name || '' }}</span>
                    <v-col cols="8">
                        <v-autocomplete
                            :items="techniques"
                            :return-object="false"
                            :hint="`${techniques.id}, ${techniques.name}`"
                            item-value="id"
                            v-model="item.techniqueId"
                            item-text="name"
                            solo
                            dense
                            variant="solo-filled"
                            label="Укажите технику"
                        ></v-autocomplete>
                    </v-col>

                    <v-col cols="4">
                        <v-text-field
                            v-if="getService(item.techniqueId)?.slug !== 'ramp' && getService(item.techniqueId)?.slug !== 'self' && getService(item.techniqueId)?.slug !== 'without_unloading'"
                            label="Кол-во операции"
                            v-model="item.count_operations"
                            solo
                            dense
                        ></v-text-field>
                    </v-col>

                    <v-col cols="12">
                        <v-autocomplete
                            v-if="getService(item.techniqueId)?.slug !== 'ramp' && getService(item.techniqueId)?.slug !== 'self' && getService(item.techniqueId)?.slug !== 'without_unloading'"
                            :items="users"
                            :return-object="false"
                            :hint="`${users.id}, ${users.full_name}`"
                            item-value="id"
                            v-model="item.userId"
                            item-text="full_name"
                            solo
                            dense
                            multiple
                            variant="solo-filled"
                            label="Укажите сотрудников"
                        ></v-autocomplete>
                    </v-col>

                    <v-col cols="12" class="text-right">
                        <v-btn @click="removeRow(index)" color="pink pink-lighten-4" outlined>Убрать</v-btn>
                    </v-col>

                </v-row>

                <v-row>
                    <v-col cols="12" class="text-right">
                        <v-btn @click="addRow" color="green green-accent-1" outlined>Добавить услугу</v-btn>
                    </v-col>
                </v-row>

                <v-row v-if="cargoTask.type === 'receive'">
                    <v-col cols="7">
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

                    <v-col cols="5">
                        <v-text-field
                            label="Укажите кв.м"
                            v-model="square"
                            solo
                            dense
                        ></v-text-field>
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

<style>
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
/*.v-text-field input {
    font-size: 14px !important;
}*/
.v-text-field .v-label {
    font-size: 13px;
}
.v-input--selection-controls {
    margin-top: 0px !important;
}
.v-text-field__details {
    display: none;
}
.service-span {
    margin-top: 10px;
}
</style>
