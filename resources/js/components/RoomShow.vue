<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.rendered.name}} <span v-show="this.rendered.visible">VISIBLE</span>
                    </h1>

                    <div class="card-action">
                        <a :href="route('rooms.index')">
                            <i class="fa fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>

                </div>

                <div class="card-body no-padding">
                    <div class="row">
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Address:</span>  {{this.rendered.address}}</div>
                        <div class="col-md-6 col-sm-12"><span class="text-bold">City:</span> {{this.rendered.city}}</div>
                    </div>

                    <div class="row padding">
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Latitude:</span>  {{this.rendered.latitude}}</div>
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Longitude:</span> {{this.rendered.longitude}}</div>
                    </div>

                    <div class="row padding">
                        <div class="col-md-12 col-sm-12"><span class="text-bold">Url:</span>  {{this.rendered.url}}</div>
                    </div>

                    <div class="row padding">
                        <div class="col-md-12">
                            <p class="text-bold">Description: </p>
                            <div v-html="rendered.description"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>


    export default {
        name: "RoomShow",
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

    h1 {
        span {
            font-size: 10px;
            padding: 5px 8px;
            background: green;
            color: white;
            border-radius: 7px;
            display: inline-block;
            align-items: end;
        }
    }

</style>
