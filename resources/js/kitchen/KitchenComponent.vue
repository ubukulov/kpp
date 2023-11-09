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
                    Столовая
                    <v-icon>mdi-room-service</v-icon>
                </v-tab>

                <v-tab v-if="user.id !== 1238" href="#tab-2">
                    Отчеты
                    <v-icon>mdi-check-box-multiple-outline</v-icon>
                </v-tab>
            </v-tabs>

            <v-tabs-items v-model="tab">

                <v-tab-item
                    :value="'tab-1'"
                >
                    <v-card flat>
                        <div class="container-fluid">
                            <kitchen :cashier="user"></kitchen>
                        </div>

                    </v-card>
                </v-tab-item>

                <v-tab-item
                    :value="'tab-2'"
                >
                    <v-card flat>
                        <div class="container-fluid">
                            <kitchen-report
                                :cashier="user"
                                :companies="companies"
                            ></kitchen-report>
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
