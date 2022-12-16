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
                            v-if="source==='admin' ||source==='reviewer'" ></i>
                    </h1>
                    <div class="card-action" v-if="source==='author'">
                        <a :href="route('posts.create')">
                            <i class="fa fa-plus-circle fa-3x fa-fw" aria-hidden="true"></i>
                        </a>

                    </div>
                    <div class="card-action" v-show="source!=='author'">
                        <span>Filter By State: </span>
                        <select name="status" v-model="state" @change="onChange()" class="form-group">
                            <option value="">All</option>
                            <option v-for="status in json_statuses" :key="status.id" :value="status.id">
                                {{status.name}}
                            </option>
                        </select>
                    </div>

                </div>
                <div class="card-body no-padding">
                    <div class="table-responsive">
                        <table class="table card-table table-striped">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Title</th>
                                <th v-show="source=='admin'">Authors</th>
                                <th>Topic</th>
                                <th>Template</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th v-if="source==='admin' ||source==='reviewer'">
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
                                <td v-show="source=='admin'">
                                    {{item.user_fk.name}} {{item.user_fk.surname}} <span
                                    v-show="item.authors"> - </span><span
                                    v-for="(author, index) in item.authors">{{author.firstname}} {{author.lastname}} <span
                                    v-if="index+1 != item.authors.length">-&nbsp;</span></span>
                                </td>
                                <td>{{item.category_fk.name}}</td>
                                <td>{{item.template_fk.name}}</td>
                                <td> {{ format(new Date(item.created_at), 'dd/MM/yyyy') }}</td>
                                <td><span :style="`background-color:${item.state_fk.color}`" class="post-status">{{item.state_fk.name}}</span>
                                </td>
                                <td v-if="source=='admin'">

                                    <span v-for="reviews in item.users">

                                        <i class="far fa-circle" v-if="!reviews.checked"
                                           data-toggle="tooltip" data-placement="top"
                                           :title="reviews.name+' '+ reviews.surname"></i>
                                        <i class="fas fa-circle" v-if="reviews.checked" data-toggle="tooltip"
                                           data-placement="top" :title="reviews.name+' '+ reviews.surname"></i></span>
                                </td>

                                <td v-if="source=='reviewer'">

                                    <span v-for="reviews in item.users">
                                        <span v-if="reviews.id === json_user.id">
                                        <i class="far fa-circle" v-if="!reviews.checked"
                                           data-toggle="tooltip" data-placement="top"
                                           :title="reviews.name+' '+ reviews.surname"></i>
                                        <i
                                            class="fas fa-circle" v-if="reviews.checked" data-toggle="tooltip"
                                            data-placement="top" :title="reviews.name+' '+ reviews.surname"></i></span>
                                    </span>
                                </td>

                                <td class="text-right">
                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.show', item.id).withQuery({ source: source })">

                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs" :href="'../reviews/create?id='+ item.id"
                                       v-if="source==='reviewer' && item.state ==3">
                                        <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.link', {id: item.id})"
                                       v-if="source==='admin'">
                                        <i class="fas fa-link fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.valid', {id: item.id})"
                                       v-if="source==='admin'">
                                        <i class="fas fa-clipboard-check fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.edit', {id: item.id})"
                                       v-if="source==='author' && item.state == 1">
                                        <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>


                                    <a class="btn btn-danger btn-xs" v-on:click="deleteItem(item.id, $event)"
                                       v-if="source==='admin' || item.state ==1">
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
    import {format} from 'date-fns';
    import Modal from "./Modal";

    export default {
        name: "PostTable",
        components: {
            'vue-pagination': Pagination,
            'modal': Modal
        },
        props: ['title', 'items', 'role', 'reviews', 'user', 'statuses', 'source'],
        data: () => {
            return {
                rendered: {},
                pages: 0,
                perpage: 20,
                page: 1,
                renderedPaginate: [],
                backup_filter: [],
                json_role: {},
                json_reviews: [],
                json_user: {},
                json_statuses: {},
                state: '',
                format,
                isModalVisible: false,
                modalHTML: {
                    title: "Papers Guide",
                    body: `<div>
                        <p>In this section you can manage the papers.</p>
                        <p>In the table in the center of the page you can, for each item, carry out operations.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-eye fa-1x fa-lg"></i> you to see the details of this item.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-link fa-1x fa-lg"></i> allows administrators to assign this paper to reviewers.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-clipboard-check fa-1x fa-lg"></i> allows administrators to read the reviews and to accept or reject the paper.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-pencil-alt fa-1x fa-lg"></i> allows a reviewers to write a review.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-minus-circle fa-1x fa-lg"></i> allows you to delete this item.</p>
                        </div>
                    `
                }
            }
        },
        mounted() {

            this.json_role = JSON.parse(this.role);
            this.json_reviews = JSON.parse(this.reviews);
            this.json_user = JSON.parse(this.user);
            this.json_statuses = JSON.parse(this.statuses);
            this.rendered = JSON.parse(this.items);
            this.backup_filter = this.rendered;
            console.log(this.json_user);
            this.pages = this.rendered.length;
            this.paginateData(this.page - 1, this.perpage);
            this.checkedReviews(this.rendered, this.json_reviews)
            console.log(this.rendered);
            //jquery('#cover').filemanager('image', '', false);
            console.log(Ziggy.namedRoutes);
            console.log(this.source);

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

            onChange() {

                this.rendered = this.backup_filter;
                if (this.state != '') {

                    this.rendered = this.rendered.filter(item => item.state == this.state);
                }

                this.pages = this.rendered.length;
                this.paginateData(this.page - 1, this.perpage);
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

    .post-status {
        padding: 5px 10px;
        border-radius: 20px;
    }

</style>
