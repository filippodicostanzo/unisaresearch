<template>
    <div class="card">
        <div class="card-header"><h5>{{title}}</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-8"><h2>{{count}}</h2></div>
                <div class="col-4 text-right">
                    <i :class="'fas fa-'+icon+ ' fa-3x'"></i>
                </div>
            </div>
        </div>
        <div class="card-footer"><i :class="'fas fa-chart-line'"></i>  +
            <span v-if="type==='users'" class="text-bold">{{users_lastmonth}} Users </span>
            <span v-if="type==='posts'" class="text-bold">{{posts_lastmonth}} Paper </span>
            <span v-if="type==='categories'" class="text-bold">{{categories_lastmonth}} Topics </span>
            in {{month}}</div>
    </div>
</template>

<script>

    import { isThisMonth, format } from 'date-fns'
    export default {
        name: "CardWidget",
        props: ['data', 'title', 'count', 'icon', 'type'],
        data: () => {
            return {
                users_lastmonth: 0,
                posts_lastmonth:0,
                categories_lastmonth:0,
                rendered: [],
                month: ''
            }
        },
        mounted() {

            this.month= format(new Date(), 'MMMM');

            if (this.data) {
                console.log(JSON.parse(this.data));
            }

            if (this.type === 'users') {
                this.rendered = JSON.parse(this.data)

                this.rendered.forEach(item => {
                    if (isThisMonth(new Date(item.created_at))) {
                        this.users_lastmonth ++;
                    }
                })

            }

            if (this.type === 'posts') {
                this.rendered = JSON.parse(this.data)

                this.rendered.forEach(item => {
                    if (isThisMonth(new Date(item.created_at))) {
                        this.posts_lastmonth ++;
                    }
                })

            }


            if (this.type === 'categories') {
                this.rendered = JSON.parse(this.data)

                this.rendered.forEach(item => {
                    if (isThisMonth(new Date(item.created_at))) {
                        this.categories_lastmonth ++;
                    }
                })

            }

        }
    }
</script>

<style lang="scss" scoped>
    .card {
        .card-header {
            h5 {
                font-family: "Montserrat";
                color: lightgray;
            }
        }

        .card-body {
            h2 {
                font-family: "Montserrat";
            }

            i {
                color: lightgrey;
            }
        }
    }

</style>
