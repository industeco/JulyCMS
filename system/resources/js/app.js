/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
window.Sortable = require('sortablejs');

window.Vue = require('vue');

// import ElementUI from 'element-ui';
// Vue.use(ElementUI);

import draggable from 'vuedraggable';
Vue.component('draggable', draggable);

// import * as moment from 'moment';
// window.moment = moment;

import CKEditor from 'ckeditor4-vue';
Vue.use( CKEditor );

import MediaUpload from './components/MediaUpload.vue';
Vue.component('jc-media-upload', MediaUpload);

import Contextmenu from './components/Contextmenu.vue';
Vue.component('jc-contextmenu', Contextmenu);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// const app = new Vue({
//     el: '#app',
// });
