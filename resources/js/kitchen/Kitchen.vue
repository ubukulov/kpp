<template>
    <v-app>
        <v-main>
            <v-container>
                <div v-if="info" v-html="infoHtml"></div>
                <div class="form-group">
                    <label>Отсканируйте или введите ID пользователя</label><br>
                    <textarea onfocus="this.value=''" @keyup.enter="checkScanCode()" ref="username" id="username" v-model="username" cols="40" rows="4" style="border:1px solid red;"></textarea>
                </div>

                <div class="form-group">
                    <button type="button" @click="checkScanCode()" class="btn btn-success nicebtn2">Внести</button>
                </div>

                <v-dialog v-model="dialog"  persistent max-width="700px">

                    <v-card>
                        <v-card-title>
                            <span class="headline">Выберите тип обеда</span>
                        </v-card-title>
                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="5">
                                        <img v-if="user.image !== null" :src="user.image" width="280" alt="">
                                        <img v-else src="/img/default-user-image.png" width="280" alt="">
                                    </v-col>
                                    <v-col cols="7">
                                        <div id="userinfo">
                                            <ul>
                                                <li>Ф.И.О: <strong id="as_fio">{{fullname}}</strong></li>
                                                <li>Компания: <strong id="as_com">{{company_name}}</strong></li>
                                                <li>Обедов на сегодня: <strong id="as_cnt">{{count}}</strong></li>
                                            </ul>
                                        </div>
                                    </v-col>

                                    <v-col cols="12">
                                        <div class="content_mw" v-if="user.count !== 3">
                                            <v-btn color="success" :disabled="isDisable1" @click="addAshana(1)">Стандарт</v-btn>
                                            <v-btn color="success" :disabled="isDisable2" @click="addAshana(2)">Булочки</v-btn>
                                        </div>

                                        <div class="content_mw" v-if="user.count === 3">
                                            <h2 style="font-size: 30px; color: red;"><strong>Ваш лимит обедов за сегодня исчерпан</strong></h2>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" @click="cancelOperation()">Отменить</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>

                <template>
                    <v-card>
                        <v-card-title>
                            <v-autocomplete
                                :items="options"
                                :hint="`${options.id}, ${options.text}`"
                                item-value="id"
                                v-model="option_id"
                                item-text="text"
                                @change="getStatisticsForOption()"
                            ></v-autocomplete>
                            <v-spacer></v-spacer>
                            <v-text-field
                                v-model="search"
                                append-icon="mdi-magnify"
                                label="Search"
                                single-line
                                hide-details
                            ></v-text-field>
                        </v-card-title>
                        <v-data-table
                            :headers="headers"
                            :items="items"
                            :search="search"
                        >
                            <template v-slot:item.sum="{ item }">
                                {{ parseInt(item.stan) + parseInt(item.bul) }}
                            </template>
                        </v-data-table>
                    </v-card>
                </template>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
    import axios from 'axios';
    export default {
        data(){
            return {
                dialog: false,
                info: false,
                infoHtml: "",
                username: "",
                fullname: "",
                company_name: "",
                count: 0,
                user_id: 0,
                isDisable1: false,
                isDisable2: false,
                search: '',
                user: [],
                headers: [
                    {
                        text: 'Наименование',
                        align: 'start',
                        filterable: false,
                        value: 'short_ru_name',
                    },
                    { text: 'Стандарт', value: 'stan' },
                    { text: 'Булочки', value: 'bul' },
                    { text: 'Общий', value: 'sum' },
                ],
                items: {},
                options: [
                    {
                        id: 1,
                        text: 'За сегодня'
                    },
                    {
                        id: 2,
                        text: 'За вчера'
                    },
                    {
                        id: 3,
                        text: 'За неделю'
                    },
                ],
                option_id: 1
            }
        },
        props: [
            'cashier'
        ],
        methods: {
            cancelOperation(){
                this.dialog = false;
                this.username = "";
                setTimeout(function() {
                    document.getElementById("username").focus();
                }, 0);
                this.isDisable1 = false;
                this.isDisable2 = false;
            },
            addAshana(type){
                if(type === 1) {
                    this.isDisable1 = true;
                } else {
                    this.isDisable2 = true;
                }
                let formData = new FormData();
                formData.append('din_type', type);
                formData.append('user_id', this.user_id);
                formData.append('cashier_id', this.cashier.id);
                axios.post('ashana/fix-changes', formData)
                    .then(res => {
                        this.info = true;
                        let din_name = res.data.din_type === 1 ? 'Обед Стандарт' : 'Булочки';
                        this.infoHtml = '<h2 style="color: green; font-size: 30px;">1 <strong>' + din_name + '</strong> записан на <strong>';
                        this.infoHtml = this.infoHtml + res.data.full_name + '</strong></h2></br><h2 style="color: green; font-size: 30px;">Талонов за сегодня: <strong>' + res.data.count + '</h2>';
                        this.dialog = false;
                        this.isDisable1 = false;
                        this.isDisable2 = false;
                        this.getStatisticsForOption();
                    })
                    .catch(err => {
                        console.log(err)
                    });
                this.username = "";
                setTimeout(function() {
                    document.getElementById("username").focus();
                }, 0);
            },
            getStatisticsForOption(){
                axios.get(`ashana/get-statistics/${this.option_id}`)
                    .then(res => {
                        console.log(res.data);
                        this.items = res.data;
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            checkScanCode(){
                let formData = new FormData();
                let scan_code = this.username;
                formData.append('username', scan_code);
                this.infoHtml = '';
                if (scan_code !== '') {
                    axios.post('ashana/get-user-info', formData)
                        .then(res => {
                            let data = res.data;
                            this.user = data;
                            this.fullname = data.full_name;
                            this.company_name = data.company_name;
                            this.count = data.count;
                            this.user_id = data.user_id;
                            this.dialog = true;
                        })
                        .catch(err => {
                            console.log(err);
                            if(err.response.status === 404) {
                                this.info = true;
                                this.infoHtml = '<strong style="font-size: 30px; color: red;">' + this.username +'  - не найден</strong>';
                            }
                            if(err.response.status === 406) {
                                this.info = true;
                                this.infoHtml = '<strong style="font-size: 30px; color: red;">' + err.response.data.message +'</strong>';
                            }
                        })
                } else {
                    this.dialog = false
                }
            }
        },
        mounted(){
            this.$refs.username.focus()
        },
        created(){
            this.items = this.getStatisticsForOption();
        }
    }
</script>

<style scoped>
    .nicebtn2 {
        background: #9C6;
        font-weight: bold;
        color: #DA4C3C;
        padding: 6px 10px 6px 10px;
        display: inline-block;
        margin-right: 10px;
    }
    .close {
        display: none;
    }
    #panel, #header,.menu-items-body,.menu-items-header,#bx-im-bar {
        display: none;
    }
    #boxes #dialog {
        width: 475px;
        height: 303px;
        padding: 10px;
        background-color: #ffffff;
    }
    #boxes .window {
        position: absolute;
        left: 0;
        top: 0px;
        -top: 40px;
        width: 440px;
        height: 200px;
        display: none;
        z-index: 9999;
        padding: 20px;
        overflow: hidden;
    }
    #userinfo strong {
        color: #000;
    }
    #userinfo ul {
        list-style: none;
    }
    strong {
        font-weight: bold;
    }
    .green_h2 {
        color: green;
        font: 300 32px/36px 'arian_amuregular', sans-serif;
        margin-bottom:
    }
    .bigerror {
        font-size: 30px;
        color: red;
    }
</style>
