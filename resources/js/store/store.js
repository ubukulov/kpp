import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        modalVisible: false,
        cargoModalVisible: false,
        cargoEditModalVisible: false,
        wagonCreateModalVisible: false,
        user: null,
        shifts: []
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
        SHOW_WAGON_MODAL(state) {
            state.wagonCreateModalVisible = true;
        },
        HIDE_WAGON_MODAL(state) {
            state.wagonCreateModalVisible = false;
        },
        SET_USER(state, userData) {
            state.user = userData;
        },
        CLEAR_USER(state) {
            state.user = null;
        },
        SET_SHIFTS(state, shifts) {
            state.shifts = shifts;
        },
        CLEAR_SHIFTS(state) {
            state.shifts = [];
        }
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
        showWagonModal({ commit }) {
            commit('SHOW_WAGON_MODAL');
        },
        hideWagonModal({ commit }) {
            commit('HIDE_WAGON_MODAL');
        },
        login({ commit }, userData) {
            // сюда можно добавить API-запрос к Laravel
            commit('SET_USER', userData);
            sessionStorage.setItem('user', JSON.stringify(userData)); // сохраняем в сессию
        },
        logout({ commit }) {
            commit('CLEAR_USER');
            sessionStorage.removeItem('user');
        },
        loadUserFromStorage({ commit }) {
            const savedUser = sessionStorage.getItem('user');
            if (savedUser) {
                commit('SET_USER', JSON.parse(savedUser));
            }
        },
        saveShifts({ commit }, shifts) {
            commit('SET_SHIFTS', shifts);
            sessionStorage.setItem('shifts', JSON.stringify(shifts)); // сохраняем на фронте
        },
        loadShiftsFromStorage({ commit }) {
            const savedShifts = sessionStorage.getItem('shifts');
            if (savedShifts) {
                commit('SET_SHIFTS', JSON.parse(savedShifts));
            }
        },
        clearShifts({ commit }) {
            commit('CLEAR_SHIFTS');
            sessionStorage.removeItem('shifts');
        }
    },
    getters: {
        isModalVisible: state => state.modalVisible,
        isCargoModalVisible: state => state.cargoModalVisible,
        isWagonCreateModalVisible: state => state.wagonCreateModalVisible,
        currentUser: state => state.user,
        userRole: state => (state.user ? state.user.role : null),
        isAuthenticated: state => !!state.user,
        isDispatcher: state => {
            return state.user && Array.isArray(state.user.roles)
                ? state.user.roles.some(role => role.slug === 'kt-operator')
                : false;
        },
        isReceiverDeliverer: state => {
            return state.user && Array.isArray(state.user.roles)
                ? state.user.roles.some(role => role.slug === 'kt-priemosdatchik')
                : false;
        },
        currentShifts: state => state.shifts
    }
});
