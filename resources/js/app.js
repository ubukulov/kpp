/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vuetify from "vuetify";
import 'vuetify/dist/vuetify.min.css'

window.Vue = require('vue');
Vue.use(Vuetify);
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/CabinetComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('cabinet-component', require('./components/CabinetComponent.vue').default);
Vue.component('driver-component', require('./components/DriverComponent.vue').default);
Vue.component('kpp-component', require('./components/KppComponent.vue').default);
Vue.component('kpp-form', require('./components/KppForm.vue').default);
Vue.component('prev-permits', require('./components/PrevPermits.vue').default);
Vue.component('fix-date-out-time', require('./components/FixDateOutTime.vue').default);
Vue.component('scan-go', require('./components/SCANGO.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    vuetify: new Vuetify(),
});
