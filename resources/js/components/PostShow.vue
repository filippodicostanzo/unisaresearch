<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.rendered.title}} <span v-if="rendered.state_fk"
                                                      :style="`background-color:${rendered.state_fk.color}`">{{rendered.state_fk.name}}</span>
                    </h1>

                    <div class="card-action">
                        <a :href="route('posts.'+source)" v-if="source">
                            <i class="fa fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>

                </div>


                <div class="card-body no-padding">
                    <div class="row pt-3">
                        <div class="col-md-6 col-sm-12" v-if="json_role.name!=='supervisor'"><span class="text-bold">Submitted By: </span>
                            {{rendered.user_fk.name}} {{rendered.user_fk.surname}}
                        </div>
                        <div class="col-md-6 col-sm-12" v-if="json_role.name!=='supervisor'"><span class="text-bold">Co Authors: </span>
                            <span
                                v-for="(author, index) in this.rendered.authors">{{author.firstname}} {{author.lastname}} <span
                                v-if="index+1 != rendered.authors.length">-&nbsp;</span></span>
                        </div>

                        <div class="col-md-6 col-sm-12"><span class="text-bold">Template: </span>
                            {{rendered.template_fk.name}}
                        </div>
                        <div class="col-md-6 col-sm-12" v-if="rendered.category_fk"><span
                            class="text-bold">Topic:</span>
                            {{rendered.category_fk.name}}
                        </div>

                    </div>

                </div>
            </div>

            <div class="card" v-for="(field, index) in nameFields">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{field.name}}
                    </h1>
                </div>

                <div class="card-body no-padding">
                    <div class="row pt-3">
                        <div class="col-md-12 col-sm-12">
                            <div v-html="`${fields[index]}`"></div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        Extra
                    </h1>
                </div>

                <div class="card-body no-padding">
                    <div class="row pt-3">
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Tags: </span>{{rendered.tags}}
                        </div>
                        <div class="col-md-6 col-sm-12"  v-show="rendered.pdf!='' && rendered.pdf!=null"><span class="text-bold">PDF: </span><a
                            :href="rendered.pdf" class="btn button btn-primary" target="_blank">Download</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" v-if="json_comment.comment!==null">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        Evaluation
                    </h1>
                </div>

                <div class="card-body no-padding">
                    <div class="row pt-3">
                        <div class="col-md-12 col-sm-12">
                            <div v-html="`${json_comment.comment}`"></div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>

<script>


    export default {
        name: "PostShow",
        props: ['item', 'role', 'source','comment'],
        data: () => {
            return {
                rendered: {
                    user_fk: {},
                    template_fk: {},
                },
                authors: [],
                fields: [],
                nameFields: [],
                json_role: {},
                json_comment:{}

            }
        },
        mounted() {
            this.json_role = JSON.parse(this.role);
            this.rendered = JSON.parse(this.item);
            this.json_comment=JSON.parse(this.comment);
            this.nameFields = JSON.parse(this.rendered.template_fk.fields);
            this.createFields();
            console.log(this.rendered);
            console.log(this.comment);

        },
        methods: {
            createFields() {
                if (this.rendered.fields_1 != '') {
                    this.fields.push(this.rendered.field_1);
                }
                if (this.rendered.fields_2 != '') {
                    this.fields.push(this.rendered.field_2);
                }
                if (this.rendered.fields_3 != '') {
                    this.fields.push(this.rendered.field_3);
                }
                if (this.rendered.fields_4 != '') {
                    this.fields.push(this.rendered.field_4);
                }
                if (this.rendered.fields_5 != '') {
                    this.fields.push(this.rendered.field_5);
                }
                if (this.rendered.fields_6 != '') {
                    this.fields.push(this.rendered.field_6);
                }
                if (this.rendered.fields_7 != '') {
                    this.fields.push(this.rendered.field_7);
                }
                if (this.rendered.fields_8 != '') {
                    this.fields.push(this.rendered.field_8);
                }
                if (this.rendered.fields_9 != '') {
                    this.fields.push(this.rendered.field_9);
                }


            },

        },
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
