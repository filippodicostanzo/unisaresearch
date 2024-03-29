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
                        ></i>
                    </h1>
                    <div class="card-action">
                        <a :href="route('statuses.create')">
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
                                <th>Color</th>
                                <th>Visible</th>
                                <th class="text-right">Options</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr v-for="(item,k) in renderedPaginate" :key="k">

                                <td>{{item.id}}</td>
                                <td>{{item.name}}</td>
                                <td>
                                    <div class="box-color" :style="`background-color:${item.color}`"></div>
                                </td>
                                <td><span v-if="item.visible==1"><i class="fa fas fa-check"></i> </span> <span v-else><i
                                    class="fa fas fa-close"></i></span></td>
                                <td class="text-right">
                                    <a class="btn btn-default btn-xs" :href="route('statuses.show', {id: item.id})">
                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-default btn-xs"
                                       :href="route('statuses.edit', {id: item.id})">
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
    import Modal from './Modal'

    export default {
        name: "StatusTable",
        components: {
            'vue-pagination': Pagination,
            'modal': Modal
        },
        props: ['title', 'items'],
        data: () => {
            return {
                currentPath: '',
                rendered: {},
                pages: 0,
                perpage: 20,
                page: 1,
                renderedPaginate: [],
                isModalVisible: false,
                modalHTML: {
                    title: "Status Guide",
                    body: `<div>
                        <p>In this section you can manage the status of the papers</p>
                        <p>If you click on the button  <i class="fa fa-info-circle fa-fw"></i> you can add a new status.</p>
                        <p>In the table in the center of the page you can, for each item, carry out operations.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-eye fa-1x fa-lg"></i> allows you to see the details of this item.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-pencil-alt fa-1x fa-lg"></i> allows you to modify this item.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-minus-circle fa-1x fa-lg"></i> allows you to delete this item.</p>
                        <p>the states are very important and it is possible to activate and deactivate them, as well as define a color for
each individual state.</p>
<p>You can add a new status for papers, but only the status with the Ids from 1 to 5 are included in the filters in the papers list.</p>
                        </div>
                    `
                }
            }
        },
        mounted() {
            this.currentPath = window.location.pathname;
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
                        .delete("/admin/statuses/" + id)
                        .then(response => {

                            if (response.status == 200) {
                                window.location.href = '/admin/statuses'
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
    .box-color {
        width: 20px;
        height: 20px;
    }
</style>
