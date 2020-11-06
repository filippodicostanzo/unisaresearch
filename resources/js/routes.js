import AuthorCreate from './components/AuthorCreate.vue';
import AuthorTable from './components/AuthorTable.vue';

export const routes = [
    { path: '/vue', component: AuthorCreate, name: 'Home' },
    { path: '/vue/example', component: AuthorTable, name: 'Example' }
];
