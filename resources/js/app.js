/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import UserTable from "./components/UserTable";


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
import PostTable from "./components/PostTable";
import UserCreate from "./components/UserCreate";
import TemplateShow from "./components/TemplateShow";
import CategoryShow from "./components/CategoryShow";
import AuthorShow from "./components/AuthorShow";
import UserShow from "./components/UserShow";
import PostShow from "./components/PostShow";
import StatusTable from "./components/StatusTable";
import StatusCreate from "./components/StatusCreate";
import StatusShow from "./components/StatusShow";
import ReviewTable from "./components/ReviewTable";
import ReviewCreate from "./components/ReviewCreate";
import PostValidate from "./components/PostValidate";
import EditionCreate from "./components/EditionCreate";
import EditionTable from "./components/EditionTable";
import EditionShow from "./components/EditionShow";
import ReviewersTable from "./components/ReviewersTable";

window.route = route;
window.Ziggy = Ziggy;

Vue.prototype.$http = axios;
Vue.prototype.$_ = lodash;
Vue.use(Vuelidate);
Vue.use( CKEditor );

Vue.mixin({
    methods: {
        route: (name, params, absolute, config = Ziggy) => route(name, params, absolute, config),
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
        'edition-table': EditionTable,
        'edition-create': EditionCreate,
        'edition-show': EditionShow,
        'template-table': TemplateTable,
        'template-create': TemplateCreate,
        'template-show': TemplateShow,
        'category-table': CategoryTable,
        'category-create': CategoryCreate,
        'category-show': CategoryShow,
        'author-table': AuthorTable,
        'author-create': AuthorCreate,
        'author-show': AuthorShow,
        'post-table': PostTable,
        'post-show': PostShow,
        'post-validate': PostValidate,
        'review-table': ReviewTable,
        'review-create': ReviewCreate,
        'reviewers-table': ReviewersTable,
        'status-table': StatusTable,
        'status-create': StatusCreate,
        'status-show': StatusShow,
        'user-table': UserTable,
        'user-create': UserCreate,
        'user-show': UserShow
    }
});

/*
import jquery from 'jquery';
jquery(document).ready(function($) {
    console.log('a');
    $("#lfm_pdf").filemanager('file')
});
*/
