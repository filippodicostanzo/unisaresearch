<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.rendered.firstname}} {{this.rendered.lastname}}
                    </h1>

                    <div class="card-action">
                        <a :href="route('authors.index')">
                            <i class="fa fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>

                </div>

                <div class="card-body no-padding">
                    <div class="row">
                        <div class="col-md-6 col-sm-12"><span class="text-bold">First Name</span>  {{this.rendered.firstname}}</div>
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Lastname:</span> {{this.rendered.lastname}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>


    export default {
        name: "CategoryShow",
        props: ['item'],
        data: () => {
            return {
                rendered: {},
            }
        },
        mounted() {
            this.rendered = JSON.parse(this.item);
            console.log(this.rendered);
        },
        methods: {},
    }
</script>

<style scoped lang="scss">

</style>
