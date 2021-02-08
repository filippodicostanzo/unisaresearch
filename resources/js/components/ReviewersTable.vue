<template>
    <div class="row">

        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.title}}
                    </h1>
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
                                <th>Assigned Papers</th>
                            </tr>
                            </thead>
                            <tbody>


                            <tr v-for="(item,k) in renderedPaginate" :key="k">

                                <td>{{item.id}}</td>
                                <td>{{item.name}}</td>
                                <td>{{item.surname}}</td>
                                <td>{{item.email}}</td>
                                <td>{{item.count}}</td>

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
        name: "ReviewersTable",
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
            console.log(this.rendered);
            this.pages = this.rendered.length;
            this.paginateData(this.page - 1, this.perpage)

        },
        methods: {
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
