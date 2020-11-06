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
        <div class="card-footer">Alba</div>
    </div>
</template>

<script>
    export default {
        name: "CardWidget",
        props: ['data', 'title', 'count', 'icon'],
        mounted() {
            console.log(this.data);
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
            i{
                color: lightgrey;
            }
        }
    }

</style>
