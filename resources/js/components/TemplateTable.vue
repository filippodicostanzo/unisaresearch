<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.title}}
                        <i class="fa fa-info-circle fa-fw pointer"
                            aria-hidden="true"
                            @click="showModal"></i>
                    </h1>
                    <div class="card-action">
                        <a href="templates/create">
                            <i class="fa fa-plus-circle fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body no-padding">
                    <div class="table-responsive">
                        <table class="table card-table table-striped">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Active</th>
                                <th class="text-right">Options</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr v-for="(item,k) in renderedPaginate" :key="k">

                                <td>{{item.id}}</td>
                                <td>{{item.name}}</td>
                                <td> <span v-if="item.active==1"><i class="fa fas fa-check"></i> </span> <span v-else><i class="fa fas fa-close"></i></span></td>
                                <td class="text-right">
                                    <a class="btn btn-default btn-xs" :href="route('templates.show', {id: item.id})">
                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-default btn-xs"
                                       :href="route('templates.edit', {id: item.id})">
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
        <modal v-show="isModalVisible" :data="modalHTML" @close="closeModal" />
    </div>
</template>

<script>
    import Pagination from 'vue-pagination-2';
    import Modal from "./Modal";

    export default {
        name: "TemplateTable",
        components: {
            'vue-pagination': Pagination,
            'modal': Modal,
        },
        props: ['title', 'items'],
        data: () => {
            return {
                rendered: {},
                pages: 0,
                perpage: 20,
                page: 1,
                renderedPaginate: [],
                isModalVisible: false,
                modalHTML: {
                    title: "Template Guide",
                    body: `<div>
                        <p>In this section you can manage the templates for the submission of papers.</p>
                        <p>If you click on the button  <i class="fa fa-info-circle fa-fw"></i> you can add a new template.</p>
                        <p>In the table in the center of the page you can, for each item, carry out operations.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-eye fa-1x fa-lg"></i> allows you to see the details of this item.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-pencil-alt fa-1x fa-lg"></i> allows you to modify this item.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-minus-circle fa-1x fa-lg"></i> allows you to delete this item.</p>
                        <p>The templates are important because they define which fields the authors of the papers must fill in.</p>
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
                        .delete("/admin/templates/" + id)
                        .then(response => {

                            if (response.status == 200) {
                                window.location.href = '/admin/templates'
                            }
                        })
                        .catch(error => {
                            alert(error.message)
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
