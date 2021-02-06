<template>
    <div class="card">
        <div class="card-header">
            <h2>Latest Paper</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped card-table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Topic</th>
                        <th scope="col" v-if="json_role.name==='researcher'">Authors</th>
                        <th scope="col">Sumbitted</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Options</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,k) in renderedPaginate" :key="k">
                        <th scope="row">{{item.id}}</th>
                        <td>{{item.title}}</td>
                        <td>{{item.category_fk.name}}</td>
                        <td  v-if="json_role.name=='researcher'"><span
                            v-for="(author, index) in item.authors ">{{author.firstname}} {{author.lastname}} <span
                            v-if="index+1 != item.authors.length"> - </span></span></td>
                        <td> {{ format(new Date(item.created_at), 'dd/MM/yyyy') }}</td>
                        <td><span :style="`background-color:${item.state_fk.color}`" class="post-status">{{item.state_fk.name}}</span>
                        </td>

                        <td class="text-right">
                            <a class="btn btn-default btn-xs" :href="route('posts.show', {id: item.id})">
                                <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                            </a>

                            <a class="btn btn-default btn-xs" :href="'admin/reviews/create?id='+ item.id"
                               v-if="json_role.name==='supervisor' && item.state ==3">
                                <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                            </a>

                            <a class="btn btn-default btn-xs"
                               :href="route('posts.link', {id: item.id})" v-if="json_role.name==='superadministrator' || json_role.name==='administrator'">
                                <i class="fas fa-link fa-1x fa-lg" aria-hidden="true"></i>
                            </a>

                            <a class="btn btn-default btn-xs"
                               :href="route('posts.valid', {id: item.id})" v-if="json_role.name==='superadministrator' || json_role.name==='administrator'">
                                <i class="fas fa-clipboard-check fa-1x fa-lg" aria-hidden="true"></i>
                            </a>

                            <a class="btn btn-default btn-xs"
                               :href="route('posts.edit', {id: item.id})" v-if="json_role.name==='researcher' && item.state == 1">
                                <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                            </a>


                            <a class="btn btn-danger btn-xs" v-on:click="deleteItem(item.id, $event)" v-if="json_role.name==='superadministrator' || json_role.name==='administrator'">
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
</template>

<script>
    import {format} from 'date-fns'
    import Pagination from 'vue-pagination-2';

    export default {
        name: "CardTable",
        props: ['data', 'role'],
        components: {
            'vue-pagination': Pagination
        },
        data: () => {
            return {
                posts: [],
                format,
                pages: 0,
                perpage: 20,
                page: 1,
                renderedPaginate: [],
                json_role: {}
            }
        },
        mounted() {
            this.json_role = JSON.parse(this.role);
            this.posts = JSON.parse(this.data);
            console.log(this.posts);
            /*
                        this.posts.forEach((post) => {
                            post.json_authors = JSON.parse(post.json_authors)
                        })
            */
            this.pages = this.posts.length;
            this.paginateData(this.page - 1, this.perpage)

            console.log(this.posts);
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
                this.renderedPaginate = this.posts.slice(start, end);
            }
        }
    }
</script>

<style scoped>

    .post-status {
        padding: 5px 10px;
        border-radius: 20px;
    }

</style>
