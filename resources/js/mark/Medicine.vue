<template>
    <div class="row mt-5">
        <v-card>
            <v-tabs
                v-model="tab"
                background-color="red-lighten-2 accent-4"
                dark
                icons-and-text
            >
                <v-tabs-slider></v-tabs-slider>

                <v-tab href="#tab-1">
                    Распечатка
                    <v-icon>mdi-shoe-heel</v-icon>
                </v-tab>

                <v-tab href="#tab-2">
                    Агрегация
                    <v-icon>mdi-medical-bag</v-icon>
                </v-tab>

            </v-tabs>

            <v-tabs-items v-model="tab">
                <v-tab-item
                    :value="'tab-1'"
                >
                    <v-card flat>
                        <div class="container-fluid mt-5">
                            <v-row>
                                <v-col cols="12">
                                    <div class="form-group mt-10">
                                        <v-select
                                            :items="markings"
                                            :hint="`${markings.id}, ${markings.number}`"
                                            item-value="id"
                                            v-model="mark_id"
                                            label="Выберите Заявку"
                                            outlined
                                            item-text="number"
                                            @change="getSeria()"
                                        ></v-select>
                                    </div>

                                    <div class="form-group mt-2">
                                        <v-select
                                            :items="series"
                                            :hint="`${series.id}, ${series.line3}`"
                                            item-value="line3"
                                            v-model="seria"
                                            label="Выберите серия"
                                            outlined
                                            item-text="line3"
                                        ></v-select>
                                    </div>
                                </v-col>
                                <v-col>
                                    <h4>Товар</h4>

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

                                    <div class="form-group">
                                        <v-btn
                                            color="light-green"
                                            @click="printProduct()"
                                        >
                                            Распечатать
                                        </v-btn>
                                    </div>
                                </v-col>

                                <v-col>
                                    <h4>Короба/Паллеты</h4>
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
                                    <div class="form-group">
                                        <v-select
                                            :items="options"
                                            :hint="`${options.id}, ${options.value}`"
                                            item-value="id"
                                            v-model="option_id"
                                            label="Опция"
                                            outlined
                                            item-text="value"
                                        ></v-select>
                                    </div>

                                    <div class="form-group">
                                        <v-btn
                                            color="light-green"
                                            @click="requestSSCC"
                                        >
                                            Распечатать
                                        </v-btn>
                                    </div>
                                </v-col>
                            </v-row>

                        </div>

                    </v-card>
                </v-tab-item>

                <v-tab-item
                    :value="'tab-2'"
                >
                    <mark-aggregation></mark-aggregation>
                </v-tab-item>
            </v-tabs-items>
        </v-card>

        <v-dialog
            v-model="dialog"
            max-width="590"
            style="margin-top: 250px;"
        >
            <v-card>
                <v-card-title class="text-h5">
                    Внимание!!!
                </v-card-title>
                <v-card-text>
                    Проверьте корректность распечатанных этикеток
                </v-card-text>
                <v-card-actions>
                    <v-btn
                        color="green darken-1"
                        text
                        @click="confirmPrintFalse"
                    >
                        Не корректно
                    </v-btn>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="green darken-1"
                        text
                        @click="confirmPrint(1)"
                    >
                        Корректно
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-overlay :value="overlay">
            <v-progress-circular
                indeterminate
                size="64"
            ></v-progress-circular>
        </v-overlay>

        <v-dialog v-model="dialogTable" width="800px">
            <v-card>
                <v-card-title>
                    <span class="headline">Печать SSCC</span>
                </v-card-title>
                <v-card-subtitle>
                    <v-text-field
                        v-model="search"
                        label="Search"
                        prepend-inner-icon="mdi-magnify"
                        hide-details
                        single-line
                    ></v-text-field>
                </v-card-subtitle>

                <v-card-text>
                    <v-btn @click="printSSCC(selected)" color="green">
                        Печать
                    </v-btn>
                </v-card-text>
                <v-card-text>
                    <v-data-table
                        v-model="selected"
                        :headers="headers"
                        :items="sscc"
                        :search="search"
                        item-value="code"
                        :items-per-page="5"
                        return-object
                        show-select
                    >
                        <template v-slot:item.action="{ item }">
                            <v-btn color="green" @click="handlePrint(item.code, item.id)">
                                Печать
                            </v-btn>
                        </template>
                    </v-data-table>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green" text @click="dialogTable = false, dialog = true">
                        Закрыть
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

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
                prints: [],
                print_id: 0,
                mark_id: 0,
                markings: [],
                errors: [],
                container_number: '',
                corop_number: '',
                gtin_number: '',
                series: [],
                seria: '',
                tab: '',
                info_result: '',
                isCustoms: true,
                dialog: false,
                craneDialog: false,
                overlay: false,
                container_ships: [],
                container_ship_id: -1,
                other_container_ship: '',
                start_date: '',
                dialogTable: false,
                items_count: [
                    {'id': 0, "value": "Не указано"},
                    {'id': 10, "value": 10},
                    {'id': 20, "value": 20},
                    {'id': 30, "value": 30},
                    {'id': 40, "value": 40},
                    {'id': 50, "value": 50},
                ],
                count: 0,
                options: [
                    {'id': 1, "value": "Короба"},
                    {'id': 2, "value": "Паллеты"},
                ],
                option_id: 1,
                sscc: [],
                selected: [],
                postJson: [],
                search: '',
                headers: [
                    {
                        text: 'SSCC',
                        align: 'start',
                        value: 'code',
                    },
                    {
                        text: 'Actions',
                        align: 'end',
                        value: 'action',
                        sortable: false
                    }
                ]
            }
        },
        methods: {
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
            },
            requestSSCC(){
                let formData = new FormData();
                formData.append('mark_id', this.mark_id);
                formData.append('printer_id', this.print_id);
                formData.append('option_id', this.option_id);
                formData.append('seria', this.seria);
                axios.post('/marking-manager/generate/sscc', formData)
                    .then(res => {
                        this.dialog = true;
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            confirmPrintFalse() {
                this.dialog = false;
                let formData = new FormData();
                formData.append('mark_id', this.mark_id);
                formData.append('option_id', this.option_id);
                axios.post('/marking-manager/get-sscc', formData)
                    .then(res => {
                        this.dialog = false;
                        this.dialogTable = true;
                        this.sscc = res.data;
                        console.log(this.sscc)
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            confirmPrint(type){
                let formData = new FormData();
                formData.append('type', type);
                formData.append('mark_id', this.mark_id);
                formData.append('option_id', this.option_id);
                axios.post('/marking-manager/confirm-generated-sscc', formData)
                    .then(res => {
                        this.dialog = false;
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            getSeria(){
                this.overlay = true;
                axios.get(`/marking-manager/${this.mark_id}/get-seria`)
                    .then(res => {
                        this.overlay = false;
                        this.series = res.data;
                    })
                    .catch(err => {
                        this.overlay = false;
                        console.log(err)
                    })
            },
            handlePrint(sscc, sscc_id) {
                let ssccData = new FormData()
                this.postJson.push({
                    ID: sscc_id,
                    SSCC: sscc
                });
                ssccData.append('sscc', JSON.stringify(this.postJson));
                ssccData.append('printer_id', this.print_id);
                axios.post('/marking-manager/aggregation/print', ssccData)
                .then(res => {
                    this.postJson = [];
                })
                .catch(err => {
                    console.log(err)
                })
            },
            printSSCC(selectedSscc) {
                selectedSscc.forEach(item => {
                    this.postJson.push({
                        ID: item.id,
                        SSCC: item.code
                    })
                });
                let dataSscc = FormData()
                dataSscc.append('sscc', this.postJson)
                axios.post('', dataSscc)
                .then(res => {
                    this.postJson = []
                })
                .catch(err => log(err))
            },
            printProduct(){
                let formData = new FormData();
                formData.append('mark_id', this.mark_id);
                formData.append('printer_id', this.print_id);
                formData.append('seria', this.seria);
                axios.post('/marking-manager/aggregation/print-product', formData)
                .then(res => {
                    console.log(res)
                })
                .catch(err => {
                    console.log(err);
                })
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

</style>
