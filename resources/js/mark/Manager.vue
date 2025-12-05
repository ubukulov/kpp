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

        <v-app id="inspire">
            <v-card>
                <v-tabs
                    v-model="tab"
                    background-color="deep-purple accent-4"
                    dark
                    icons-and-text
                >
                    <v-tabs-slider></v-tabs-slider>

                    <v-tab href="#tab-1">
                        Обувь
                        <v-icon>mdi-shoe-heel</v-icon>
                    </v-tab>

                    <v-tab href="#tab-2">
                        Лекарство
                        <v-icon>mdi-medical-bag</v-icon>
                    </v-tab>

                </v-tabs>

                <v-tabs-items v-model="tab">
                    <v-tab-item
                        :value="'tab-1'"
                    >
                        <Shoes @show-loading="showLoading"></Shoes>
                    </v-tab-item>

                    <v-tab-item
                        :value="'tab-2'"
                    >
                        <v-card flat>
                            <div class="container-fluid">
                                <Medicine></Medicine>
                            </div>
                        </v-card>
                    </v-tab-item>
                </v-tabs-items>
            </v-card>
        </v-app>

        <Loading :show="overlay"></Loading>
    </div>
</template>

<script>
    import Medicine from "./Medicine.vue";
    import Shoes from "./Shoes.vue";
    import Loading from "../components/system/Loading.vue";
    export default {
        components: {
            Medicine,
            Shoes,
            Loading
        },
        props: [
            'user'
        ],
        data(){
            return {
                tab: null,
                overlay: false
            }
        },
        methods: {
            showLoading(data) {
                this.overlay = data.overlay;
            }
        }
    }
</script>

<style scoped>

</style>
