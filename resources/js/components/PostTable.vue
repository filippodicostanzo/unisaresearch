<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.title}}
                    </h1>
                    <div class="card-action" v-if="this.json_role.name==='researcher'">
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
                                <th>Created</th>
                                <th>Status</th>
                                <th v-if="json_role.name==='superadministrator' || json_role.name==='administrator'">
                                    Reviews
                                </th>
                                <th class="text-right">Options</th>
                            </tr>
                            </thead>
                            <tbody>

                            <div>
                            </div>


                            <tr v-for="(item,k) in renderedPaginate" :key="k">

                                <td>{{item.id}}</td>
                                <td>{{item.title}}</td>
                                <td>{{item.category_fk.name}}</td>
                                <td>{{item.template_fk.name}}</td>
                                <td> {{ format(new Date(item.created_at), 'dd/MM/yyyy') }}</td>
                                <td><span :style="`background-color:${item.state_fk.color}`" class="post-status">{{item.state_fk.name}}</span>
                                </td>
                                <td v-if="json_role.name==='superadministrator' || json_role.name==='administrator'">

                                    <span v-for="reviews in item.users"><i class="far fa-circle" v-if="!reviews.checked"
                                                                           data-toggle="tooltip" data-placement="top"
                                                                           :title="reviews.name+' '+ reviews.surname"></i><i
                                        class="fas fa-circle" v-if="reviews.checked" data-toggle="tooltip"
                                        data-placement="top" :title="reviews.name+' '+ reviews.surname"></i></span>
                                </td>
                                <td class="text-right">
                                    <a class="btn btn-default btn-xs" :href="route('posts.show', {id: item.id})">
                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs" :href="'reviews/create?id='+ item.id"
                                       v-if="json_role.name==='supervisor'">
                                        <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.link', {id: item.id})"
                                       v-if="json_role.name==='superadministrator' || json_role.name==='administrator'">
                                        <i class="fas fa-link fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.valid', {id: item.id})"
                                       v-if="json_role.name==='superadministrator' || json_role.name==='administrator'">
                                        <i class="fas fa-clipboard-check fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.edit', {id: item.id})"
                                       v-if="json_role.name==='researcher' && item.state == 1">
                                        <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>


                                    <a class="btn btn-danger btn-xs" v-on:click="deleteItem(item.id, $event)"
                                       v-if="json_role.name==='superadministrator' || json_role.name==='administrator' || item.state ==1">
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
    import {format} from 'date-fns';

    export default {
        name: "PostTable",
        components: {
            'vue-pagination': Pagination
        },
        props: ['title', 'items', 'role', 'reviews'],
        data: () => {
            return {
                rendered: {},
                pages: 0,
                perpage: 10,
                page: 1,
                renderedPaginate: [],
                json_role: {},
                json_reviews: [],
                format,
            }
        },
        mounted() {

            this.json_role = JSON.parse(this.role);
            this.json_reviews = JSON.parse(this.reviews);
            this.rendered = JSON.parse(this.items);
            this.pages = this.rendered.length;
            this.paginateData(this.page - 1, this.perpage);
            this.checkedReviews(this.rendered, this.json_reviews)
            console.log(this.rendered);
            //jquery('#cover').filemanager('image', '', false);

        },
        methods: {
            deleteItem(id, e) {
                e.preventDefault();
                let alt = confirm('Are you sure to delete this item?');
                if (alt) {
                    this.$http
                    //.delete("/admin/posts/" + id)

                        .post('/admin/posts/' + id, {_method: 'delete'})

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
                    input.value = items[0].url
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
            },
            checkedReviews(items, reviews) {
                items.map((el) => {
                    el.users.map((us) => {
                        reviews.map((rew) => {
                            if (us.pivot.user_id == rew.supervisor && us.pivot.post_id == rew.post) {
                                us.checked = true;
                            }
                        })
                    })
                });

            }


        },
    }
</script>

<style scoped>

    .post-status {
        padding: 5px 10px;
        border-radius: 20px;
    }

</style>
