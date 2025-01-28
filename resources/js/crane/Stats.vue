<template>
    <div>
        <div class="service_header">
            <div>
                <v-icon @click="returnServices">mdi-arrow-left</v-icon>
            </div>

            <div>
                <h4>Статистики</h4>
            </div>
        </div>

        <v-timeline
            dense
        >
            <v-timeline-item
                class="mb-4"
                color="grey"
                icon-color="grey lighten-2"
                small
                v-for="(item, i) in items"
                :key="i"
            >
                <v-row>
                    <v-col>
                        {{ item.dt }} : <br> Кол-во операции: <strong>{{ item.cnt }}</strong>
                    </v-col>
                </v-row>
            </v-timeline-item>
        </v-timeline>
    </div>
</template>

<script>
    import axios from 'axios'
    export default {
        data(){
            return {
                items: []
            }
        },
        methods: {
            getStatsForMe(){
                axios.get('/container-crane/stats/getting-stats-for-me')
                .then(res => {
                    this.items = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
            },
            returnServices(){
                this.$emit('back', 'back')
            }
        },
        created(){
            this.getStatsForMe();
        }
    }
</script>
<style scoped>

</style>
