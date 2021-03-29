<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.rendered.title}} <span v-show="this.rendered.active">ACTIVE</span>
                    </h1>

                    <div class="card-action">
                        <a :href="route('events.index')">
                            <i class="fa fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>

                </div>


                <div class="card-body no-padding">

                    <div class="row padding" v-if="this.rendered.authors">
                        <div class="col-md-12 col-sm-12"><span class="text-bold">Authors:</span>
                            {{this.rendered.authors}}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Type:</span> <span
                            class="text-capitalize">{{this.rendered.type}}</span></div>
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Room:</span>
                            {{this.rendered.room_fk.name}}
                        </div>
                    </div>

                    <div class="row padding">
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Start:</span> {{ format(new Date(this.rendered.start), 'dd/MM/yyyy hh:mm')}}</div>
                        <div class="col-md-6 col-sm-12"><span class="text-bold">End:</span>  {{ format(new Date(this.rendered.end), 'dd/MM/yyyy hh:mm')}}</div>
                    </div>


                    <div class="row padding" v-if="rendered.description">
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

    import {format} from 'date-fns';

    export default {
        name: "Event",
        props: ['item'],
        data: () => {
            return {
                rendered: {
                    room_fk: {},
                    start: new Date(),
                    end: new Date
                },
                format
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
