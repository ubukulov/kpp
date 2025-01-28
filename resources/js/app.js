/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vuetify from "vuetify";
import 'vuetify/dist/vuetify.min.css'
import Vuex from 'vuex';

window.Vue = require('vue');
Vue.use(Vuetify);
Vue.use(Vuex);

import store from "./store/store";

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/CabinetComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('driver-component', require('./components/DriverComponent.vue').default);
Vue.component('kpp-component', require('./components/KppComponent.vue').default);
Vue.component('kpp-form', require('./components/KppForm.vue').default);
Vue.component('prev-permits', require('./components/PrevPermits.vue').default);
Vue.component('fix-date-out-time', require('./components/FixDateOutTime.vue').default);
Vue.component('scan-go', require('./components/SCANGO.vue').default);
Vue.component('personal-control', require('./components/PersonalControl.vue').default);
Vue.component('search', require('./components/SearchComponent.vue').default);
Vue.component('kt', require('./crane/KT.vue').default);
Vue.component('kt-operator-task-create', () => import('./container/KT_OperatorTaskCreate.vue'));
Vue.component('kt-operator-task-edit', require('./components/KT_OperatorTaskEdit.vue').default);
Vue.component('kpp', require('./components/KPP.vue').default);
Vue.component('kpp-form2', require('./components/KppForm2.vue').default);
Vue.component('kt-operator-task', () => import('./container/KT_OperatorTask.vue'));
Vue.component('kt-controller', require('./components/KT_Controller.vue').default);
Vue.component('kt-controller-logs', require('./components/KT_ControllerLogs.vue').default);
Vue.component('kt-crane-stats', require('./crane/Stats.vue').default);
Vue.component('kitchen', require('./kitchen/Kitchen').default);
Vue.component('kitchen-component', require('./kitchen/KitchenComponent').default);
Vue.component('kitchen-report', require('./kitchen/Report').default);
Vue.component('crane-services', require('./crane/Services').default);
Vue.component('crane', require('./crane/Crane').default);
Vue.component('mark-manager', require('./mark/Manager').default);
Vue.component('technique-create-task', require('./technique/CreateTaskTechnique.vue').default);
Vue.component('technique-controller', require('./technique/TechniqueComponent.vue').default);
Vue.component('technique-medicine', require('./mark/Medicine.vue').default);
Vue.component('mark-aggregation', require('./mark/Aggregation.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    vuetify: new Vuetify(),
    store,
});
