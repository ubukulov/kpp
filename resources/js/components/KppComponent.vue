<template>
    <v-app id="inspire">
        <v-card>
            <v-system-bar
                height="30"
                dark
                color="red lighten-2"
            >
                <v-spacer></v-spacer>
                <div style="margin-right: 50px;">
                    <v-icon>mdi-account-circle-outline</v-icon>
                    {{ user.email }}
                </div>
                <div>
                    <v-icon>mdi-logout</v-icon>
                    <a href="/logout">Выйти</a>
                </div>
            </v-system-bar>
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
                    SCANGO
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
                            <kpp-form
                                :datetime="datetime"
                                :companies="companies"
                                :capacity="capacity"
                                :bodytypes="bodytypes"
                                :directions="directions"
                                :user="user">
                            </kpp-form>
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
                            <scan-go></scan-go>
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
            'directions',
            'user'
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
