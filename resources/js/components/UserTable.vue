<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.title}}
                    </h1>
                    <div class="card-action">
                        <a :href="route('users.create')">
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
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="text-right">Options</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr v-for="(item,k) in renderedPaginate" :key="k">

                                <td>{{item.id}}</td>
                                <td>{{item.name}}</td>
                                <td>{{item.surname}}</td>
                                <td>{{item.email}}</td>
                                <td>{{item.role}}</td>
                                <td class="text-right">
                                    <a class="btn btn-default btn-xs" :href="route('users.show', {id: item.id})">
                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-default btn-xs"
                                       :href="route('users.edit', {id: item.id})">
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
    </div>
</template>

<script>

    import Pagination from 'vue-pagination-2';

    export default {
        name: "UserTable",
        components: {
            'vue-pagination': Pagination
        },
        props: ['title', 'items'],
        data: () => {
            return {
                rendered: {},
                pages: 0,
                perpage: 20,
                page: 1,
                renderedPaginate: []
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
                        .delete("/admin/users/" + id)
                        .then(response => {

                            if (response.status === 200) {
                                window.location.href = '/admin/users'
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
            }

        },
    }
</script>

<style scoped>

</style>
