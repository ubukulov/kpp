<script>
import axios from "axios";
import AlertSuccess from "../components/system/AlertSuccess.vue";
export default {
    components: {
        AlertSuccess
    },
    name: "Shoes",
    data() {
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
            system: {
                success: false,
                systemText: '',
                stats: ''
            }
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
            this.$emit('show-loading', {overlay: true});
            axios.get('/marking-manager/get-markings')
                .then(res => {
                    this.$emit('show-loading', {overlay: false});
                    this.markings = res.data;
                })
                .catch(err => {
                    this.$emit('show-loading', {overlay: false});
                    console.log(err)
                })
        },
        getPrinters(){
            this.$emit('show-loading', {overlay: true});
            axios.get('/marking-manager/get-printers')
                .then(res => {
                    this.$emit('show-loading', {overlay: false});
                    this.prints = res.data;
                })
                .catch(err => {
                    this.$emit('show-loading', {overlay: false});
                    console.log(err)
                })
        },
        getContainers(){
            this.$emit('show-loading', {overlay: true});
            axios.get(`/marking-manager/${this.mark_id}/get-containers`)
                .then(res => {
                    this.$emit('show-loading', {overlay: false});
                    this.containers = res.data;
                })
                .catch(err => {
                    this.$emit('show-loading', {overlay: false});
                    console.log(err)
                })
        },
        markPrint(){
            this.errors = [];
            this.system.success = false;
            this.system.systemText = '';
            this.system.stats = '';

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
                        this.$nextTick(() => {
                            this.$refs.gtinInput.$refs.input.focus()
                        })
                        this.system.success = true;
                        this.system.systemText = res.data.message;
                        this.system.stats = res.data.stats;
                    })
                    .catch(err => {
                        console.log(err)
                        this.errors.push(err.response.data.message);
                        this.system.success = false;
                        this.system.systemText = '';
                        this.system.stats = err.response.data.stats;
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

<template>
    <v-card flat>
        <div class="container-fluid">
            <v-window v-model="step">
                <v-window-item :value="1">
                    <div class="form-group mt-10">
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

                    <div class="form-group mt-10">
                        <v-select
                            :items="containers"
                            :hint="`${containers.id}, ${containers.container_number}`"
                            item-value="container_number"
                            v-model="container_number"
                            label="Выберите контейнер"
                            outlined
                            item-text="container_number"
                        ></v-select>
                    </div>

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

                    <div class="form-group mt-10">
                        <v-text-field
                            v-model="corop_number"
                            label="Сканировать внешний короб"
                            hide-details="auto"
                        ></v-text-field>
                    </div>

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
                    <v-row class="mt-10">

                        <v-col cols="12">
                            <h4><strong>{{ system.stats }}</strong></h4>
                        </v-col>

                        <v-col cols="3">
                            <v-text-field
                                ref="gtinInput"
                                v-model="gtin_number"
                                label="Сканировать товар/GTIN"
                                hide-details="auto"
                                autofocus
                            ></v-text-field>
                        </v-col>

                        <v-col cols="2">
                            <v-btn
                                color="primary"
                                depressed
                                @click="markPrint()"
                            >Распечатать</v-btn>
                        </v-col>

                        <v-col v-if="system.success" cols="12">
                            <AlertSuccess :message="system.systemText"></AlertSuccess>
                        </v-col>
                    </v-row>

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
        </div>

    </v-card>
</template>

<style scoped>

</style>
