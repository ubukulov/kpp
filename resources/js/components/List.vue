<template>
    <div>
        <div class="schedule_header">
            <div class="schedule_header_title">
                <h4>График</h4>
            </div>
            <div class="schedule_header__create">
                <v-btn @click="showModal" class="schedule__wrap__action__button">
                    <v-icon>mdi-calendar-plus</v-icon>
                </v-btn>
            </div>
        </div>
        <div class="schedule__wrap" v-for="(item, index) in items" :key="index">
            <div class="schedule__wrap__action">
                <div>
                    <p>Составил график: <br><span>{{ item.user.full_name }}</span></p>
                </div>
                <div>
                  <v-btn class="schedule__wrap__action__button">
                      <v-icon>mdi-calendar-edit</v-icon>
                  </v-btn>
                </div>
            </div>
            <hr>
            <p>Крановщик: <br><span>{{ item.crane_name }}</span></p>
            <p>Стропальщик: </p>
            <p v-for="(sl,i) in item.slinger" :key="sl"><span>{{i+1}}. {{sl.full_name}}</span></p>
        </div>

        <ScheduleCreateModal></ScheduleCreateModal>
    </div>
</template>

<script>
import axios from "axios";
import { mapActions } from 'vuex';
import ScheduleCreateModal from "./modals/ScheduleCreateModal.vue";
export default {
    components: {
        ScheduleCreateModal
    },
    data() {
        return {
            items: []
        }
    },
    methods: {
        ...mapActions(['showModal']),
        getSchedules(){
            axios.get('/container-controller/get-schedules')
                .then(res => {
                    this.items = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
        }
    },
    created(){
        this.getSchedules();
    }
}
</script>

<style lang="scss" scoped>
.schedule_header {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    border-bottom: 2px solid #000;
}
.schedule__wrap__action__button {
    background: none !important;
    border: none;
    box-shadow: none;
}
.schedule__wrap {
    margin: 10px 0;
    width: 100%;
    border: 2px solid #f5f5f5;
    padding: 10px;
    border-radius: 10px;

    span{
        font-weight: 400;
    }

    &__action {
        display: flex;
        gap: 5px;
        justify-content: space-between;
        align-items: center;
    }
}
.v-application p {
    margin-bottom: 5px;
    font-weight: bold;
}

</style>
