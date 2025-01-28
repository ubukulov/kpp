<template>
    <div>
        <div class="service_header">
            <div>
                <v-icon @click="returnServices">mdi-arrow-left</v-icon>
            </div>

            <div>
                <h4>Кран</h4>
            </div>
        </div>

        <v-row style="margin-top: 20px;">

            <v-col v-if="craneSuccess" cols="12">
                <v-alert
                    dense
                    text
                    type="success"
                >
                    "Кран, работает!"
                </v-alert>
            </v-col>

            <v-col v-if="craneFailure" cols="12">
                <v-alert
                    dense
                    border="left"
                    type="warning"
                >
                    {{ "Кран не работает. Причина: " + reasons[reason_id].text }}
                </v-alert>
            </v-col>

            <v-col cols="12">
                <v-select
                    :items="techniques"
                    label="Техника (Текущий статус)"
                    :hint="`${techniques.id}, ${techniques.name}`"
                    item-value="id"
                    v-model="technique_id"
                    item-text="name"
                >
                </v-select>
            </v-col>

            <v-col cols="12">
                <v-select
                    v-if="isCraneWorks"
                    :items="reasons"
                    label="Техника (Изменить статус)"
                    :hint="`${reasons.id}, ${reasons.text}`"
                    item-value="id"
                    v-model="reason_id"
                    item-text="text"
                >
                </v-select>
            </v-col>

            <v-col cols="12">
                <v-btn
                    v-if="reason_id !== 0 && technique_id !== 0 && isCraneWorks"
                    depressed
                    color="primary"
                    class="crane_btn"
                    @click="confirm"
                >Подтвердить</v-btn>

                <v-btn
                    v-if="technique_id !== 0 && !isCraneWorks"
                    depressed
                    color="success"
                    class="crane_btn"
                    @click="confirmWorks"
                >Кран, работает!</v-btn>
            </v-col>
        </v-row>

        <v-overlay :value="overlay">
            <v-progress-circular
                indeterminate
                size="64"
            ></v-progress-circular>
        </v-overlay>
    </div>
</template>

<script>
    import axios from "axios";

    export default {
        data(){
            return {
                techniques: [],
                technique_id: 0,
                reason_id: 0,
                reasons: [
                    {
                        id: 0, text: 'Выберите'
                    },
                    {
                        id: 1, text: 'погодные условия'
                    },
                    {
                        id: 2, text: 'поломка'
                    },
                    {
                        id: 3, text: 'техобслуживание'
                    },
                ],
                overlay: false,
                craneSuccess: false,
                craneFailure: false,
            }
        },
        methods: {
            returnServices(){
                this.$emit('back', 'back')
            },
            getTechniques(){
                axios.get('/container-crane/get-techniques')
                    .then(res => {
                        this.techniques = res.data.map(function(item){
                            if(item.status === 'works') {
                                item.name = `${item.name} (Работает)`
                            } else {
                                item.name = `${item.name} (Не работает)`
                            }
                            return item;
                        });
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
            confirm(){
                this.overlay = true;
                let formData = new FormData();
                formData.append('technique_id', this.technique_id);
                formData.append('reason', this.reasons[this.reason_id].text);
                axios.post('/container-crane/technique', formData)
                .then(res => {
                    console.log(res);
                    this.overlay = false;
                    this.craneFailure = true;
                    this.getTechniques();
                    setTimeout(function(){
                        window.location.href = '/container-crane';
                    }, 3000);
                })
                .catch(err => {
                    console.log(err);
                    this.overlay = false;
                })
            },
            confirmWorks(){
                this.overlay = true;
                let formData = new FormData();
                formData.append('technique_id', this.technique_id);
                axios.post('/container-crane/technique', formData)
                    .then(res => {
                        console.log(res);
                        this.overlay = false;
                        this.craneSuccess = true;
                        this.getTechniques();
                        setTimeout(function(){
                            window.location.href = '/container-crane';
                        }, 3000);
                    })
                    .catch(err => {
                        console.log(err);
                        this.overlay = false;
                    })
            }
        },
        computed: {
            isCraneWorks(){
                let tech_id = this.technique_id;
                let item = this.techniques.find(function(item,index,array){
                    if(item.id === tech_id) {
                        return item;
                    }
                });
                if (item && item.status === 'works') {
                    return true;
                } else {
                    return false;
                }
            }
        },
        created() {
            this.getTechniques();
        }
    }
</script>

<style scoped>
.crane_btn {
    font-size: 12px !important;
}
</style>
