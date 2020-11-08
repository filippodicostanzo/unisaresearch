/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */


require('./bootstrap');
require('./admin')
require('../../public/vendor/adminlte/dist/js/adminlte.min.js');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

import axios from 'axios';
import lodash from 'lodash'
import Vuelidate from 'vuelidate'
import CKEditor from 'ckeditor4-vue';

import route from 'ziggy-js';
import { Ziggy } from './ziggy';

import CardWidget from "./components/CardWidget";
import CardTable from "./components/CardTable";
import TemplateCreate from "./components/TemplateCreate";
import TemplateTable from "./components/TemplateTable";
import CategoryCreate from "./components/CategoryCreate";
import CategoryTable from "./components/CategoryTable";
import AuthorCreate from "./components/AuthorCreate";
import AuthorTable from "./components/AuthorTable";
import PostsTable from "./components/PostsTable";

window.route = route;
window.Ziggy = Ziggy;

Vue.prototype.$http = axios;
Vue.prototype.$_ = lodash;
Vue.use(Vuelidate);
Vue.use( CKEditor );

Vue.mixin({
    methods: {
        route: (name, params, absolute) => route(name, params, absolute, Ziggy),
    },
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */


const app = new Vue({
    el: '#app',
    components: {
        'card-widget': CardWidget,
        'card-table': CardTable,
        'template-table': TemplateTable,
        'template-create': TemplateCreate,
        'category-table': CategoryTable,
        'category-create': CategoryCreate,
        'author-table': AuthorTable,
        'author-create': AuthorCreate,
        'posts-table': PostsTable,

    }
});

