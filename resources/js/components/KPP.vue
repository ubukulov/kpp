<template>
    <v-app id="inspire">
        <v-card>
            <v-tabs
                v-model="tab"
                background-color="deep-purple accent-4"
                centered
                dark
                icons-and-text
            >
                <v-tabs-slider></v-tabs-slider>

                <v-tab href="#tab-1">
                    Регистрация въезда
                    <v-icon>mdi-boom-gate-up</v-icon>
                </v-tab>

                <v-tab href="#tab-2">
                    Предварительный пропуск
                    <v-icon>mdi-check-box-multiple-outline</v-icon>
                </v-tab>

                <v-tab href="#tab-3">
                    Фиксация выезда
                    <v-icon>mdi-boom-gate-down</v-icon>
                </v-tab>

                <v-tab href="#tab-4">
                    Контроль доступа персонала
                    <v-icon>mdi-qrcode-scan</v-icon>
                </v-tab>

                <v-tab href="#tab-5">
                    Поиск
                    <v-icon>mdi-search-web</v-icon>
                </v-tab>
            </v-tabs>

            <v-tabs-items v-model="tab">
                <v-tab-item
                    :value="'tab-1'"
                >
                    <v-card flat>
                        <div class="container-fluid">
                            <kpp-form2
                                :datetime="datetime"
                                :companies="companies"
                                :capacity="capacity"
                                :bodytypes="bodytypes">
                            </kpp-form2>
                        </div>

                    </v-card>
                </v-tab-item>

                <v-tab-item
                    :value="'tab-2'"
                >
                    <v-card flat>
                        <div class="container-fluid">
                            <prev-permits :companies="companies" ref="prev_permits"></prev-permits>
                        </div>
                    </v-card>
                </v-tab-item>

                <v-tab-item
                    :value="'tab-3'"
                >
                    <v-card flat>
                        <div class="container-fluid">
                            <fix-date-out-time></fix-date-out-time>
                        </div>
                    </v-card>
                </v-tab-item>

                <v-tab-item
                    :value="'tab-4'"
                >
                    <v-card flat>
                        <div class="container-fluid">
                            <personal-control></personal-control>
                        </div>
                    </v-card>
                </v-tab-item>

                <v-tab-item
                    :value="'tab-5'"
                >
                    <v-card flat>
                        <div class="container-fluid">
                            <search></search>
                        </div>
                    </v-card>
                </v-tab-item>
            </v-tabs-items>
        </v-card>
    </v-app>
</template>

<script>
    export default {
        data () {
            return {
                tab: null,
            }
        },
        props: [
            'datetime',
            'companies',
            'capacity',
            'bodytypes',
            'directions'
        ],
        created(){
            console.log(this.tab)
        }
    }
</script>

<style scoped>
    .v-tabs-bar__content a {
        text-decoration: none;
    }
</style>
