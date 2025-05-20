<template>
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12">
                    <div style="width: 45%;text-align: left;float: left;">
                        <span style="text-align: left;"><strong>{{ user.full_name }}</strong></span>
                    </div>
                    <div style="width: 45%;float: right;text-align: right;">
                        <a style="color: #000; text-align: right" href="/logout">Выйти</a>
                    </div>
                </div>
            </div>
        </div>

        <template>
            <v-window v-model="step">
                <v-window-item :value="1">
                    <br>
                    <div class="form-group">
                        <v-select
                            :items="markings"
                            :hint="`${markings.id}, ${markings.number}`"
                            item-value="id"
                            v-model="mark_id"
                            label="Выберите Заявку"
                            outlined
                            item-text="number"
                        ></v-select>
                    </div>

                    <div class="form-group">
                        <v-select
                            :items="prints"
                            :hint="`${prints.id}, ${prints.printer_name}`"
                            item-value="id"
                            v-model="print_id"
                            label="Выберите принтер"
                            outlined
                            item-text="printer_name"
                        ></v-select>
                    </div>

                    <v-divider></v-divider>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primary"
                            depressed
                            @click="nextStep()"
                        >
                            Далее
                        </v-btn>
                    </v-card-actions>
                </v-window-item>

                <v-window-item :value="2">

                    <v-select
                        :items="containers"
                        :hint="`${containers.id}, ${containers.container_number}`"
                        item-value="container_number"
                        v-model="container_number"
                        label="Выберите контейнер"
                        outlined
                        item-text="container_number"
                    ></v-select>

                    <v-divider></v-divider>

                    <v-card-actions>
                        <v-btn
                            text
                            @click="step--"
                        >
                            Назад
                        </v-btn>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primary"
                            depressed
                            @click="nextStep()"
                        >
                            Далее
                        </v-btn>
                    </v-card-actions>
                </v-window-item>

                <v-window-item :value="3">
                    <br>
                    <v-text-field
                        v-model="corop_number"
                        label="Сканировать внешний короб"
                        hide-details="auto"
                    ></v-text-field>

                    <v-divider></v-divider>

                    <v-card-actions>
                        <v-btn
                            text
                            @click="step--"
                        >
                            Назад
                        </v-btn>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="primary"
                            depressed
                            @click="nextStep()"
                        >
                            Далее
                        </v-btn>
                    </v-card-actions>
                </v-window-item>

                <v-window-item :value="4">
                    <br>
                    <v-text-field
                        v-model="gtin_number"
                        label="Сканировать товар/GTIN"
                        hide-details="auto"
                    ></v-text-field>
                    <br>
                    <v-btn
                        color="primary"
                        depressed
                        @click="markPrint()"
                    >Распечатать</v-btn>

                    <v-divider></v-divider>

                    <v-card-actions>
                        <v-btn style="font-size: 14px !important;" @click="step--">
                            Назад
                        </v-btn>

                        <v-spacer></v-spacer>

                    </v-card-actions>
                </v-window-item>

                <div class="col-md-12">
                    <p v-if="errors.length" style="margin-bottom: 0px !important;">
                        <b>Пожалуйста исправьте указанные ошибки:</b>
                    <ul style="color: #cc0000; padding-left: 15px; list-style: circle; text-align: left;">
                        <li v-for="error in errors">{{error}}</li>
                    </ul>
                    </p>
                </div>
            </v-window>
        </template>

        <v-overlay :value="overlay">
            <v-progress-circular
                indeterminate
                size="64"
            ></v-progress-circular>
        </v-overlay>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        props: [
            'user'
        ],
        data(){
            return {
                step: 1,
                prints: [],
                techniques: [],
                print_id: 0,
                mark_id: 0,
                markings: [],
                errors: [],
                containers: [],
                technique_id: 0,
                container_number: '',
                corop_number: '',
                gtin_number: '',
                info_result: '',
                isCustoms: true,
                craneDialog: false,
                rows: [],
                row_id: 0,
                places: [],
                place_id: 0,
                floors: [],
                floor_id: 0,
                zones: [],
                zone_id: 0,
                current_zone_id: 0,
                is_row: true,
                is_place: true,
                is_floor: true,
                is_receive: true,
                is_shipping: true,
                is_moving: true,
                current_container_address: '',
                overlay: false,
                container_ships: [],
                container_ship_id: -1,
                other_container_ship: '',
                start_date: '',
                bottom_nav: 'operation_cont',
                containersInRow: []
            }
        },
        methods: {
            nextStep(){
                this.errors = [];
                if(this.mark_id === 0) {
                    this.errors.push('Выберите заявку');
                }
                if(this.print_id === 0) {
                    this.errors.push('Выберите принтер');
                }

                if(this.step === 3) {
                    if(this.corop_number === '') {
                        this.errors.push('Укажите номер короба');
                    }
                    if(this.errors.length === 0) {
                        this.step++;
                    }
                }

                if(this.step === 2) {
                    if(this.container_number === '') {
                        this.errors.push('Выберите контейнер');
                    }
                    if(this.errors.length === 0) {
                        this.step++;
                    }
                }

                if(this.step === 1) {
                    if(this.errors.length === 0) {
                        this.getContainers();
                        this.step++;
                    }
                }

            },
            getMarkings(){
                this.overlay = true;
                axios.get('/marking-manager/get-markings')
                    .then(res => {
                        this.overlay = false;
                        this.markings = res.data;
                        console.log(res.data);
                    })
                    .catch(err => {
                        this.overlay = false;
                        console.log(err)
                    })
            },
            getPrinters(){
                this.overlay = true;
                axios.get('/marking-manager/get-printers')
                    .then(res => {
                        this.overlay = false;
                        this.prints = res.data;
                        console.log(res.data);
                    })
                    .catch(err => {
                        this.overlay = false;
                        console.log(err)
                    })
            },
            getContainers(){
                this.overlay = true;
                axios.get(`/marking-manager/${this.mark_id}/get-containers`)
                    .then(res => {
                        this.overlay = false;
                        this.containers = res.data;
                        console.log(res.data);
                    })
                    .catch(err => {
                        this.overlay = false;
                        console.log(err)
                    })
            },
            markPrint(){
                this.errors = [];
                if(this.gtin_number === '') {
                    this.errors.push('Укажите GTIN');
                }
                if(this.errors.length === 0) {
                    let formData = new FormData();
                    formData.append('mark_id', this.mark_id);
                    formData.append('print_id', this.print_id);
                    formData.append('container_number', this.container_number);
                    formData.append('box_number', this.corop_number);
                    formData.append('gtin_number', this.gtin_number);
                    axios.post('/marking-manager/command-print', formData)
                    .then(res => {
                        this.gtin_number = '';
                        console.log(res)
                    })
                    .catch(err => {
                        console.log(err)
                    })
                }
            }
        },
        created() {
            this.getMarkings();
            this.getPrinters();
        },
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
    .v-dialog__content {
        top: 30% !important;
        position: absolute !important;
    }
</style>
