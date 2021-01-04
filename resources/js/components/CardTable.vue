<template>
    <div class="card">
        <div class="card-header">
            <h2>Latest Posts</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Authors</th>
                        <th scope="col">Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">View</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,k) in renderedPaginate" :key="k">
                        <th scope="row">{{item.id}}</th>
                        <td>{{item.title}}</td>
                        <td><span v-for="(author, index) in item.authors ">{{author.firstname}} {{author.lastname}} <span v-if="index+1 != item.authors.length"> - </span></span></td>
                        <td> {{ format(new Date(item.created_at), 'dd/MM/yyyy')  }}</td>
                        <td><span :style="`background-color:${item.state_fk.color}`" class="post-status">{{item.state_fk.name}}</span></td>
                        <td><a :href="route('posts.single', {id: item.id})">View</a></td>
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
    import { format } from 'date-fns'
    import Pagination from 'vue-pagination-2';

    export default {
        name: "CardTable",
        props: ['data'],
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
                renderedPaginate: []
            }
        },
        mounted() {
            console.log(this.data);
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
        methods:{
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

    .post-status{
        color: white;
        padding: 3px;
    }

</style>
