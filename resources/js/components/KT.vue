<template>
    <div class="row">
        <div class="col-md-12">
            <template>
                <v-window v-model="step">
                    <v-window-item :value="1">
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <a style="color: #fff;" class="btn btn-primary" href="/logout">Выйти из аккаунта</a>
                            </div>
                            <br><br>
                        </div>
                        <br>
                        <div class="form-group">
                            <v-select
                                :items="zones"
                                :hint="`${zones.zone}, ${zones.title}`"
                                item-value="zone"
                                v-model="zone_id"
                                label="Зона"
                                outlined
                                item-text="title"
                            ></v-select>
                        </div>

                        <div class="form-group">
                            <v-select
                                :items="techniques"
                                label="Тип техники"
                                :hint="`${techniques.id}, ${techniques.name}`"
                                item-value="id"
                                v-model="technique_id"
                                outlined
                                item-text="name"
                            ></v-select>
                        </div>

                        <v-divider></v-divider>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                v-if="zone_id !==0, technique_id !== 0"
                                color="primary"
                                depressed
                                @click="step++"
                            >
                                Далее
                            </v-btn>
                        </v-card-actions>
                    </v-window-item>

                    <v-window-item :value="2">
                        <v-row>
                            <v-col cols="8">
                                <v-text-field
                                    v-model="container_number"
                                    label="Номер контейнера"
                                    hide-details="auto"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="4">
                                <v-btn
                                    depressed
                                    color="primary"
                                    @click="getInfoForContainer()"
                                >
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </v-btn>
                            </v-col>
                        </v-row>

                        <v-divider></v-divider>

                        <div v-if="info_container != ''" style="font-size: 20px;
                        line-height: 20px;
                        margin-bottom: 30px;
                        font-weight: bold;
                        border: 1px solid yellowgreen;
                        padding: 10px;" v-html="info_container">

                        </div>

                        <v-card-actions>
                            <v-row>
                                <v-col>
                                    <button style="width: 100%;" @click="step++" :disabled="is_receive" type="button" class="btn" :class="[is_receive == true ? 'btn-light' : 'btn-success']">Размещение</button>
                                </v-col>
                                <v-col>
                                    <button @click="isReceive()" type="button" :disabled="is_shipping" class="btn" :class="[is_shipping == true ? 'btn-light' : 'is_shipping']">Выдача</button>
                                </v-col>
                                <v-col>
                                    <button @click="isMoving()" type="button" :disabled="is_moving" class="btn" :class="[is_moving == true ? 'btn-light' : 'is_moving']">Перемещение</button>
                                </v-col>
                            </v-row>
                        </v-card-actions>

                        <v-divider></v-divider>

                        <v-card-actions>
                            <v-btn
                                :disabled="step === 1"
                                text
                                @click="step--"
                            >
                                Назад
                            </v-btn>
                        </v-card-actions>
                    </v-window-item>

                    <!-- Пункт Размещение -->
                    <v-window-item :value="3">
                        <div class="form-group">
                            <v-select
                                :items="zones"
                                outlined
                                label="Зона"
                                :hint="`${zones.zone}, ${zones.title}`"
                                item-value="zone"
                                v-model="zone_id"
                                @change="getFreeRows()"
                                item-text="title"
                            ></v-select>
                        </div>

                        <div class="form-group">
                            <v-select
                                :disabled="is_row"
                                label="Ряд"
                                :items="rows"
                                :hint="`${rows.row}`"
                                item-value="row"
                                outlined
                                v-model="row_id"
                                item-text="row"
                                @change="getFreePlaces()"
                            ></v-select>
                        </div>

                        <div class="form-group">
                            <v-select
                                label="Место"
                                :disabled="is_place"
                                :items="places"
                                :hint="`${places.place}`"
                                item-value="place"
                                item-text="place"
                                outlined
                                v-model="place_id"
                                @change="getFreeFloors()"
                            ></v-select>
                        </div>

                        <div class="form-group">
                            <v-select
                                label="Ярус"
                                :disabled="is_floor"
                                :items="floors"
                                :hint="`${floors.floor}`"
                                item-value="floor"
                                item-text="floor"
                                outlined
                                v-model="floor_id"
                            ></v-select>
                        </div>

                        <v-divider></v-divider>

                        <v-card-actions>
                            <v-btn
                                :disabled="step === 1"
                                text
                                @click="step--"
                            >
                                Назад
                            </v-btn>
                            <v-spacer></v-spacer>
                            <v-btn
                                v-if="zone_id !==0, row_id !== 0, place_id !== 0, floor_id !== 0"
                                color="primary"
                                depressed
                                @click="step++"
                            >
                                Далее
                            </v-btn>
                        </v-card-actions>
                    </v-window-item>

                    <v-window-item :value="4">
                        <div style="padding: 10px 5px;background: green;color: #fff;" class="form-group">
                            <p style="font-size: 24px;
                            line-height: 25px;
                            font-weight: bold;">Вы размещаете контейнер <span style="color: red;">{{ container_number }}</span> по адресу <span style="color: red">{{ zone_id }}-{{ row_id }}-{{ place_id }}-{{ floor_id }}</span> ?</p>
                        </div>

                        <v-divider></v-divider>

                        <v-card-actions>
                            <v-btn style="font-size: 14px !important;" @click="step--">
                                Назад
                            </v-btn>

                            <v-spacer></v-spacer>

                            <v-btn style="font-size: 14px !important;" depressed class="primary" @click="receiveContainer()">
                                Подтвердить!
                            </v-btn>
                        </v-card-actions>
                    </v-window-item>

                    <v-window-item :value="5">
                        <div style="padding: 10px 5px;background: green;color: #fff;" class="form-group">
                            <p style="font-size: 20px;" v-html="info_result"></p>
                        </div>

                        <v-divider></v-divider>

                        <v-card-actions>

                            <v-spacer></v-spacer>

                            <v-btn class="btn btn-blue" @click="step=2">
                                Далее
                            </v-btn>
                        </v-card-actions>
                    </v-window-item>
                    <!-- ./Пункт Размещение -->

                    <!-- Пункт Перемещение -->
                    <v-window-item :value="6">
                        <div class="form-group">
                            <v-select
                                label="Зона"
                                :items="zones"
                                outlined
                                :hint="`${zones.zone}, ${zones.title}`"
                                item-value="zone"
                                v-model="zone_id"
                                @change="getFreeRows()"
                                item-text="title"
                            ></v-select>
                        </div>

                        <div class="form-group">
                            <v-select
                                label="Ряд"
                                :disabled="is_row"
                                :items="rows"
                                :hint="`${rows.row}`"
                                item-value="row"
                                outlined
                                v-model="row_id"
                                item-text="row"
                                @change="getFreePlaces()"
                            ></v-select>
                        </div>

                        <div class="form-group">
                            <v-select
                                label="Место"
                                :disabled="is_place"
                                :items="places"
                                :hint="`${places.place}`"
                                item-value="place"
                                item-text="place"
                                outlined
                                v-model="place_id"
                                @change="getFreeFloors()"
                            ></v-select>
                        </div>

                        <div class="form-group">
                            <v-select
                                label="Ярус"
                                :disabled="is_floor"
                                :items="floors"
                                :hint="`${floors.floor}`"
                                item-value="floor"
                                item-text="floor"
                                outlined
                                v-model="floor_id"
                            ></v-select>
                        </div>

                        <v-divider></v-divider>

                        <v-card-actions>
                            <v-btn
                                :disabled="step === 1"
                                text
                                @click="step=2"
                            >
                                Назад
                            </v-btn>
                            <v-spacer></v-spacer>
                            <v-btn
                                v-if="zone_id !==0, row_id !== 0, place_id !== 0, floor_id !== 0"
                                color="primary"
                                depressed
                                @click="step++"
                            >
                                Далее
                            </v-btn>
                        </v-card-actions>
                    </v-window-item>

                    <v-window-item :value="7">
                        <div style="padding: 10px 5px;background: darkorange;color: #fff;" class="form-group">
                            <p style="font-size: 24px;
    line-height: 30px;
    font-weight: bold;">Вы перемещаете контейнер <span style="color: red;">{{ container_number }}</span> из <span style="color: red;">{{ current_container_address }}</span>  В  <span style="color: red;">{{ zone_id }}-{{ row_id }}-{{ place_id }}-{{ floor_id }}</span> ?</p>
                        </div>

                        <v-divider></v-divider>

                        <v-card-actions>
                            <v-btn style="font-size: 14px !important;" @click="step--">
                                Назад
                            </v-btn>

                            <v-spacer></v-spacer>

                            <v-btn style="font-size: 14px !important;" class="primary" @click="moveContainer()">
                                Подтвердить!
                            </v-btn>
                        </v-card-actions>
                    </v-window-item>

                    <v-window-item :value="8">
                        <div style="padding: 10px 5px;background: darkorange;color: #fff;" class="form-group">
                            <p style="font-size: 20px;" v-html="info_result"></p>
                        </div>

                        <v-divider></v-divider>

                        <v-card-actions>

                            <v-spacer></v-spacer>

                            <v-btn class="btn btn-blue" @click="step=2">
                                Далее
                            </v-btn>
                        </v-card-actions>
                    </v-window-item>
                    <!-- ./Пункт Перемещение -->

                    <!-- Пункт Выдачи -->
                    <v-window-item :value="9">
                        <div style="padding: 10px 5px; background: #70aee2; color: #fff;" class="form-group">
                            <p style="font-size: 28px;
                            line-height: 30px;
                            font-weight: bold;">Вы выдаете контейнер <span style="color: red;">{{ container_number }}</span> ?</p>
                        </div>

                        <v-divider></v-divider>

                        <v-card-actions>
                            <v-btn style="font-size: 14px !important;" @click="step=2">
                                Назад
                            </v-btn>

                            <v-spacer></v-spacer>

                            <v-btn style="font-size: 14px !important;" depressed class="primary" @click="shippingContainer()">
                                Подтвердить!
                            </v-btn>
                        </v-card-actions>
                    </v-window-item>

                    <v-window-item :value="10">
                        <div style="padding: 10px 5px; background: #70aee2; color: #fff;" class="form-group">
                            <p style="font-size: 20px;" v-html="info_result"></p>
                        </div>

                        <v-divider></v-divider>

                        <v-card-actions>

                            <v-spacer></v-spacer>

                            <v-btn class="btn btn-blue" @click="step=2">
                                Далее
                            </v-btn>
                        </v-card-actions>
                    </v-window-item>
                    <!-- ./Пункт Выдачи -->
                </v-window>
            </template>

            <v-overlay :value="overlay">
                <v-progress-circular
                    indeterminate
                    size="64"
                ></v-progress-circular>
            </v-overlay>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        data(){
            return {
                step: 1,
                containers: [],
                techniques: [],
                container_id: 0,
                technique_id: 0,
                container_number: '',
                info_container: '',
                info_result: '',
                rows: [],
                row_id: 0,
                places: [],
                place_id: 0,
                floors: [],
                floor_id: 0,
                zones: [],
                zone_id: 0,
                is_row: true,
                is_place: true,
                is_floor: true,
                is_receive: true,
                is_shipping: true,
                is_moving: true,
                current_container_address: '',
                overlay: false
            }
        },
        methods: {
            getZones(){
                this.overlay = true;
                axios.get('/container-crane/get-zones')
                    .then(res => {
                        this.overlay = false;
                        this.zones = res.data;
                        console.log(this.zones)
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            getContainers(){
                axios.get('/container-crane/get-containers')
                .then(res => {
                    this.containers = res.data;
                    console.log(this.containers)
                })
                .catch(err => {
                    console.log(err)
                })
            },
            getTechniques(){
                this.overlay = true;
                axios.get('/container-crane/get-techniques')
                    .then(res => {
                        this.overlay = false;
                        this.techniques = res.data;
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            getFreeRows(){
                this.overlay = true;
                this.rows = [];
                this.places = [];
                this.floors = [];
                this.row_id = 0;
                this.place_id = 0;
                this.floor_id = 0;
                let formData = new FormData();
                formData.append('zone', this.zone_id);
                formData.append('container_id', this.container_id);
                axios.post('/container-crane/container/search-and-getting-free-rows', formData)
                    .then(res => {
                        this.overlay = false;
                        this.rows = res.data;
                        this.is_row = false;
                        console.log(this.rows)
                    })
                    .catch(err => {
                        console.log(err)
                        this.overlay = false;
                    })
            },
            getFreePlaces(){
                this.overlay = true;
                let formData = new FormData();
                this.places = [];
                this.floors = [];
                this.place_id = 0;
                this.floor_id = 0;
                formData.append('zone', this.zone_id);
                formData.append('row', this.row_id);
                formData.append('container_id', this.container_id);
                axios.post('/container-crane/container/search-and-getting-free-places', formData)
                    .then(res => {
                        this.overlay = false;
                        this.places = res.data;
                        this.is_place = false;
                        console.log(this.places)
                    })
                    .catch(err => {
                        console.log(err)
                        this.overlay = false;
                    })
            },
            getFreeFloors(){
                this.overlay = true;
                this.floors = [];
                this.floor_id = 0;
                let formData = new FormData();
                formData.append('zone', this.zone_id);
                formData.append('row', this.row_id);
                formData.append('place', this.place_id);
                formData.append('container_id', this.container_id);
                axios.post('/container-crane/container/search-and-getting-free-floors', formData)
                    .then(res => {
                        this.overlay = false;
                        this.floors = res.data;
                        this.is_floor = false;
                        console.log(this.floors)
                    })
                    .catch(err => {
                        console.log(err)
                        this.overlay = false;
                    })
            },
            receiveContainer(){
                this.overlay = true;
                let formData = new FormData();
                formData.append('zone', this.zone_id);
                formData.append('row', this.row_id);
                formData.append('place', this.place_id);
                formData.append('floor', this.floor_id);
                formData.append('container_id', this.container_id);
                formData.append('technique_id', this.technique_id);
                axios.post('/container-crane/container/receive-container-change', formData)
                .then(res => {
                    this.overlay = false;
                    console.log(res);
                    this.info_result = res.data.data;
                    this.step = 5;
                    this.container_id = 0;
                    this.container_number = '';
                    this.info_container = '';
                    this.is_receive = true;
                    this.row_id = 0;
                    this.place_id = 0;
                    this.floor_id = 0;
                    this.getContainers();
                })
                .catch(err => {
                    console.log(err)
                    this.overlay = false;
                })
            },
            getInfoForContainer(){
                this.overlay = true;
                this.row_id = 0;
                this.place_id = 0;
                this.floor_id = 0;
                let formData = new FormData();
                formData.append('container_number', this.container_number);
                axios.post('/container-crane/get-info-for-container', formData)
                .then(res => {
                    this.overlay = false;
                    this.info_container = res.data.data.text;
                    this.container_id = res.data.data.container_id;
                    this.container_number = res.data.data.container_number;
                    this.current_container_address = res.data.data.current_container_address;
                    if (res.data.data.event == 1) {
                        this.is_receive = false;
                        this.is_shipping = true;
                        this.is_moving = true;
                    }
                    if (res.data.data.event == 2) {
                        this.is_receive = true;
                        this.is_shipping = false;
                        this.is_moving = true;
                    }
                    if (res.data.data.event == 3) {
                        this.is_receive = true;
                        this.is_shipping = true;
                        this.is_moving = false;
                    }
                    console.log(res.data)
                })
                .catch(err => {
                    if(err.response.status == 404 || err.response.status == 403) {
                        this.info_container = err.response.data;
                        this.is_receive = true;
                        this.is_shipping = true;
                        this.is_moving = true;
                        this.overlay = false;
                    }
                    console.log(err.response)
                })
            },
            moveContainer(){
                this.overlay = true;
                let formData = new FormData();
                formData.append('zone', this.zone_id);
                formData.append('row', this.row_id);
                formData.append('place', this.place_id);
                formData.append('floor', this.floor_id);
                formData.append('container_id', this.container_id);
                formData.append('technique_id', this.technique_id);
                axios.post('/container-crane/container/moving-container-change', formData)
                    .then(res => {
                        console.log(res);
                        this.overlay = false;
                        this.info_result = res.data.data;
                        this.step = 8;
                        this.container_id = 0;
                        this.container_number = '';
                        this.info_container = '';
                        this.is_moving = true;
                        this.row_id = 0;
                        this.place_id = 0;
                        this.floor_id = 0;
                        this.getContainers();
                    })
                    .catch(err => {
                        console.log(err)
                        this.overlay = false;
                    })
            },
            isMoving(){
                this.overlay = true;
                // step = 6
                let formData = new FormData();
                formData.append('container_id', this.container_id);
                axios.post('/container-crane/checking-the-container-for-movement', formData)
                    .then(res => {
                        this.overlay = false;
                        this.getFreeRows();
                        console.log(res);
                        this.step = 6;
                    })
                    .catch(err => {
                        if(err.response.status == 403) {
                            this.info_container = err.response.data.data.text;
                            this.is_receive = true;
                            this.is_shipping = true;
                            this.is_moving = true;
                            this.overlay = false;
                        }
                        console.log(err.response)
                    })
            },
            isReceive(){
                this.overlay = true;
                let formData = new FormData();
                formData.append('container_id', this.container_id);
                axios.post('/container-crane/checking-the-container-for-dispensing', formData)
                    .then(res => {
                        console.log(res);
                        this.step = 9;
                        this.overlay = false;
                    })
                    .catch(err => {
                        if(err.response.status == 403) {
                            this.info_container = err.response.data.data.text;
                            this.is_receive = true;
                            this.is_shipping = true;
                            this.is_moving = true;
                            this.overlay = false;
                        }
                        console.log(err.response)
                    })
            },
            shippingContainer() {
                this.overlay = true;
                let formData = new FormData();
                formData.append('container_id', this.container_id);
                formData.append('technique_id', this.technique_id);
                axios.post('/container-crane/container/shipping-container-change', formData)
                    .then(res => {
                        console.log(res);
                        this.overlay = false;
                        this.info_result = res.data.data;
                        this.step = 10;
                        this.container_id = 0;
                        this.container_number = '';
                        this.info_container = '';
                        this.is_receive = true;
                        this.row_id = 0;
                        this.place_id = 0;
                        this.floor_id = 0;
                        this.getContainers();
                        this.is_receive = true;
                        this.is_shipping = true;
                        this.is_moving = true;
                    })
                    .catch(err => {
                        console.log(err)
                        this.overlay = false;
                    })
            }
        },
        created() {
            this.getTechniques();
            this.getZones();
        }
    }
</script>

<style scoped>
    .is_shipping {
        width: 100%;background-color: #70aee2 !important;border-color: #70aee2 !important;
    }
    .is_moving {
        width: 100%; background-color: darkorange !important;border-color: darkorange !important;
    }
    .v-application--wrap {
        min-height: auto !important;
    }
    .v-select__selection {
        font-weight: bold !important;
    }
    .v-application--is-ltr .v-text-field .v-label {
        font-size: 24px !important;
        font-weight: normal !important;
    }
</style>
