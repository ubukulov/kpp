import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        modalVisible: false,
        cargoModalVisible: false,
    },
    mutations: {
        SHOW_MODAL(state) {
            state.modalVisible = true;
        },
        HIDE_MODAL(state) {
            state.modalVisible = false;
        },
        SHOW_CARGO_MODAL(state) {
            state.cargoModalVisible = true;
        },
        HIDE_CARGO_MODAL(state) {
            state.cargoModalVisible = false;
        },
    },
    actions: {
        showModal({ commit }) {
            commit('SHOW_MODAL');
        },
        hideModal({ commit }) {
            commit('HIDE_MODAL');
        },
        showCargoModal({ commit }) {
            commit('SHOW_CARGO_MODAL');
        },
        hideCargoModal({ commit }) {
            commit('HIDE_CARGO_MODAL');
        },
    },
    getters: {
        isModalVisible: state => state.modalVisible,
        isCargoModalVisible: state => state.cargoModalVisible,
    }
});
