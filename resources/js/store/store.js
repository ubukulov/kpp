import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        modalVisible: false,
    },
    mutations: {
        // мутации для изменения состояния
        SHOW_MODAL(state) {
            state.modalVisible = true;
        },
        HIDE_MODAL(state) {
            state.modalVisible = false;
        }
    },
    actions: {
        // действия, которые могут вызывать мутации
        showModal({ commit }) {
            commit('SHOW_MODAL');
        },
        hideModal({ commit }) {
            commit('HIDE_MODAL');
        }
    },
    getters: {
        // геттеры для получения состояния
        isModalVisible: state => state.modalVisible
    }
});
