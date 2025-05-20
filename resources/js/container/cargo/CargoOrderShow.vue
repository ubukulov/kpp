<script>
import dateformat from "dateformat";
export default {
    data() {
        return {
            comments: null,
            cargoServices: [],
            cargoServiceIds: []
        }
    },
    props: [
        'cargoTask',
        'cargoStocks',
        'user'
    ],
    methods: {
        convertDateToOurFormat(dt){
            return dateformat(dt, 'dd.mm.yyyy HH:MM');
        },
        handleStartPosition(id){
            window.location.href = `/cargo-controller/cargo-task/${this.cargoTask.id}-${id}/start`;
        },
        handleEditPosition(id){
            window.location.href = `/cargo-controller/cargo-task/${this.cargoTask.id}-${id}/edit`;
        },
        saveChanges(){
            let formData = new FormData();
            formData.append('cargoTaskId', this.cargoTask.id);
            formData.append('comments', this.comments);
            formData.append('cargoServiceIds', JSON.stringify(this.cargoServiceIds));
            axios.post('/cargo-controller/cargo/tasks/changes', formData)
                .then(res => {
                    window.location.href = `/cargo-controller/cargo-task/${this.cargoTask.id}/show`;
                })
                .catch(err => {
                    console.log(err);
                })
        },
        editPossible(cargoStock) {
            if (!this.cargoTask || !cargoStock) return false; // Проверяем, есть ли данные

            console.log("editPossible computed:", this.cargoTask.type, this.cargoTask.status, cargoStock.status);

            return this.cargoTask.type === 'receive' &&
                this.cargoTask.status !== 'completed' &&
                cargoStock.status === 'received';
        },
        getCargoServices(){
            axios.get('/cargo-controller/get-cargo-services')
                .then(res => {
                    this.cargoServices = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
        }
    },
    created() {
        this.comments = (this.cargoTask.comments === 'null') ? '' : this.cargoTask.comments;
        this.getCargoServices();
        if(this.cargoTask.services.length > 0) {
            this.cargoServiceIds = this.cargoTask.services.filter(item => item.id).map(item => item.id);
        }
        console.log(this.cargoStocks)
    }
}
</script>

<template>
<v-app>
    <v-main>
        <v-container>
            <v-row>
                <div class="card-flex mb-1">
                    <div>
                        <a class="btn btn-outline-primary" href="/cargo-controller">Назад</a>
                    </div>

                    <div>
                        <a class="btn btn-outline-primary" href="/logout">Выйти</a>
                    </div>
                </div>
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
                            <p>Позиции: {{ cargoStocks.length }}</p>
                        </div>

                        <div>
                            <p v-if="cargoTask.type === 'receive'">Тип: ЗАВОЗ</p>
                            <p v-else>Тип: ВЫВОЗ</p>
                        </div>
                    </div>
                </v-col>

                <v-col
                    v-for="(item, i) in cargoStocks"
                    :key="i"
                    cols="12"
                >
                    <v-card
                        class="card-block"
                    >
                        <v-card-text>
                            <div class="card-body-block card-flex" style="border-bottom: 1px dashed;">
                                <div>
                                    <strong>Наименование</strong>
                                    <p>{{ item.cargo_name }}</p>

                                    <strong>ВинКод</strong>
                                    <p>{{ item.vin_code }}</p>
                                </div>

                                <div>
                                    <strong>Позиция</strong>
                                    <p>{{ i+1 }}</p>

                                    <strong>Статус</strong>
                                    <p v-if="item.status === 'incoming'">Не размещен</p>
                                    <p v-if="item.status === 'received'">Размещен</p>
                                    <p v-if="item.status === 'in_order'">К отбору</p>
                                    <p v-if="item.status === 'shipped'">Выдан</p>
                                </div>
                            </div>

                            <div class="card-footer-block card-flex">
                                <div>
                                    <span>Машина</span>
                                    <p>{{ item.car_number }}</p>
                                </div>
                                <div>
                                    <span>Вес (кг)</span>
                                    <p>{{ item.weight }}</p>
                                </div>
                                <div>
                                    <span>Кол-во</span>
                                    <p v-if="item.quantity_reserved">{{ item.quantity_reserved }} / {{ item.quantity }}</p>
                                    <p v-else>{{ item.quantity }}</p>
                                </div>
                                <div>
                                    <span>Дата</span>
                                        <p class="subheading">
                                            {{ convertDateToOurFormat(item.created_at) }}
                                        </p>
                                </div>
                            </div>

                            <div>
                                <button v-if="item.status === 'incoming' || item.status === 'in_order'" @click="handleStartPosition(item.id)" type="button" class="card-btn">Начать</button>
                                <button v-if="editPossible(item)" @click="handleEditPosition(item.id)" type="button" class="card-btn btn-orange">Изменить</button>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

                <hr>

                <h4 style="text-align: center;">Доп. услуги и комментарии</h4>

                <v-col cols="12">
                    <v-autocomplete
                        :items="cargoServices"
                        :return-object="false"
                        :hint="`${cargoServices.id}, ${cargoServices.name}`"
                        item-value="id"
                        v-model="cargoServiceIds"
                        item-text="name"
                        multiple
                        solo
                        variant="solo-filled"
                        label="Укажите доп услуги"
                    ></v-autocomplete>
                </v-col>

                <v-col cols="12">
                    <v-card
                        class="card-block"
                        style="padding: 10px 20px;"
                    >
                        <v-textarea label="Комментарий" v-model="comments"></v-textarea>

                        <button @click="saveChanges" type="button" class="card-btn">Сохранить</button>
                    </v-card>
                </v-col>

            </v-row>
        </v-container>
    </v-main>
</v-app>
</template>

<style scoped>
.card-block {
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
.btn-orange {
    background: orange !important;
}
</style>
