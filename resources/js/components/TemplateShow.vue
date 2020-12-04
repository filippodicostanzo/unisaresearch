<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.rendered.name}} <span v-show="this.rendered.active">ACTIVE</span>
                    </h1>

                    <div class="card-action">
                        <a :href="route('templates.index')">
                            <i class="fa fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>

                </div>

                <div class="card-body no-padding">
                    <h2>Fields</h2>
                    <ul>
                        <li v-for="field in fields"> {{
                            field.name }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>


    export default {
        name: "TemplateShow",
        props: ['item'],
        data: () => {
            return {
                rendered: {},
                fields: [],
            }
        },
        mounted() {
            this.rendered = JSON.parse(this.item);
            this.fields = JSON.parse(this.rendered.fields);
            console.log(this.rendered);
            console.log(this.fields);
        },
        methods: {},
    }
</script>

<style scoped lang="scss">

    h1 {
        span {
            font-size: 10px;
            font-size: 10px;
            padding: 5px 8px;
            /* line-height: 30px; */
            background: green;
            color: white;
            border-radius: 7px;
            display: inline-block;
            align-items: end;
        }
    }

</style>
