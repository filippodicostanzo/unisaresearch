<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.title}}
                        <i
                            class="fa fa-info-circle fa-fw pointer"
                            aria-hidden="true"
                            @click="showModal"
                            v-if="source==='admin'"></i>

                    </h1>


                    <div class="card-action" v-show="source==='author'">
                        <a :href="route('authors.create')">
                            <i class="fa fa-plus-circle fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body no-padding">
                    <div class="row">
                        <div class="col-12">
                            <p>
                                Please, add all your co-authors here. You may select them during the submission of a
                                manuscript.
                            </p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-striped">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th class="text-right">Options</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr v-for="(item,k) in renderedPaginate" :key="k">

                                <td>{{ item.id }}</td>
                                <td>{{ item.firstname }}</td>
                                <td>{{ item.lastname }}</td>
                                <td>{{ item.email }}</td>
                                <td class="text-right">
                                    <a class="btn btn-default btn-xs"
                                       :href="route('authors.show', (item.id)).withQuery({ source: source })">
                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-default btn-xs"
                                       :href="route('authors.edit', {id: item.id})">
                                        <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-danger btn-xs" v-on:click="deleteItem(item.id, $event)">
                                        <i class="fas fa-minus-circle fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                </td>

                            </tr>

                            </tbody>

                        </table>

                        <vue-pagination v-model="page" :per-page="perpage" :records="pages"
                                        @input="callbackPagination(page)"></vue-pagination>

                    </div>
                </div>
            </div>
        </div>
        <modal v-show="isModalVisible" :data="modalHTML" @close="closeModal"/>
    </div>
</template>

<script>

import Pagination from 'vue-pagination-2';
import Modal from './Modal';

export default {
    name: "AuthorTable",
    components: {
        'vue-pagination': Pagination,
        'modal': Modal,
    },
    props: ['title', 'items', 'source', 'role'],
    data: () => {
        return {
            rendered: {},
            pages: 0,
            perpage: 20,
            page: 1,
            renderedPaginate: [],
            isModalVisible: false,
            modalHTML: {
                title: "Co Authors Guide",
                body: `<div>
                        <p>In this section you can manage the co-authors of the papers.</p>
                        <p>If you click on the button  <i class="fa fa-info-circle fa-fw"></i> you can add a new co-author.</p>
                        <p>In the table in the center of the page you can, for each item, carry out operations.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-eye fa-1x fa-lg"></i> allows you to see the details of this item.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-pencil-alt fa-1x fa-lg"></i> allows you to modify this item.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-minus-circle fa-1x fa-lg"></i> allows you to delete this item.</p>
                        </div>
                    `
            }
        }
    },
    mounted() {
        this.rendered = JSON.parse(this.items);
        this.pages = this.rendered.length;
        this.paginateData(this.page - 1, this.perpage)

    },
    methods: {
        deleteItem(id, e) {
            e.preventDefault();

            let alt = confirm('Are you sure to delete this item?');
            if (alt) {
                this.$http
                    .post("/admin/authors/" + id, {_method: 'delete'})
                    .then(response => {


                        if (response.status === 200) {

                            if (window.location.href.indexOf("all") > -1) {
                                window.location.href = '/admin/authors/all'
                            } else {
                                window.location.href = '/admin/authors'
                            }
                        }
                    })
                    .catch(error => {
                        alert(error.status)
                    });

            }
        },

        callbackPagination(page) {

            let start = 0;
            let end = 0;

            if (page == 1) {
                start = page - 1;
                end = (page - 1) + this.perpage;
            } else {
                start = (page - 1) * this.perpage;
                end = start + this.perpage;
            }
            this.paginateData(start, end);
        },

        paginateData(start, end) {
            this.renderedPaginate = this.rendered.slice(start, end);
        },
        showModal() {
            this.isModalVisible = true;
        },
        closeModal() {
            this.isModalVisible = false;
        }

    },
}
</script>

<style scoped>

</style>
