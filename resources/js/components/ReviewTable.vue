<template>
    <div class="row">

        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.title}}
                    </h1>
                    <div class="card-action">
                        <a :href="route('posts.create')">
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
                                <th>Title</th>
                                <th>Category</th>
                                <th>Template</th>
                                <th class="text-right">Options</th>
                            </tr>
                            </thead>
                            <tbody>


                            <tr v-for="(item,k) in renderedPaginate" :key="k">

                                <td>{{item.id}}</td>
                                <td>{{item.title}}</td>
                                <td>{{item.category_fk.name}}</td>
                                <td> {{item.template_fk.name}}</td>
                                <td class="text-right">
                                    <a class="btn btn-default btn-xs" :href="'../../reviews/create?id='+ item.id">
                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <!--
                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.link', {id: item.id})">
                                        <i class="fas fa-link fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    -->

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
        name: "ReviewTable",
        components: {
            'vue-pagination': Pagination
        },
        props: ['title', 'items'],
        data: () => {
            return {
                rendered: {},
                pages: 0,
                perpage: 4,
                page: 1,
                renderedPaginate: []
            }
        },
        mounted() {
            this.rendered = JSON.parse(this.items);
            this.pages = this.rendered.length;
            this.paginateData(this.page - 1, this.perpage)
            //jquery('#cover').filemanager('image', '', false);

        },
        methods: {
            deleteItem(id, e) {
                e.preventDefault();
                let alt = confirm('Are you sure to delete this item?');
                if (alt) {
                    this.$http
                        .delete("/admin/posts/" + id)
                        .then(response => {

                            if (response.status === 200) {
                                window.location.href = '/admin/posts'
                            }
                        })
                        .catch(error => {
                            alert(error.message)
                        });

                }
            },
            openFileManager(e) {

                e.preventDefault();
                window.open(`/laravel-filemanager` + '?type=file', 'FileManager', 'width=900,height=600');
                //window.open(`/laravel-filemanager`, 'targetWindow', 'width=900,height=600')
                var self = this
                window.SetUrl = function (items) {
                    console.log('B');
                    console.log('C')
                    console.log(items);
                    var input = document.getElementById('lfm_pdf_input');
                    input.value=items[0].url
                    //self.form.main_image = items[0].url


                }
                return false;

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
