<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.rendered.title}} <span>{{this.role}}</span>
                    </h1>

                    <div class="card-action">
                        <a :href="route('posts.index')">
                            <i class="fa fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>

                </div>

                <div class="card-body no-padding">
                    <div class="row pt-3">
                        <div class="col-md-12 col-sm-12"><span class="text-bold">Authors:</span> <span
                            v-for="author in authors">{{author.firstname}} {{author.lastname}} - </span></div>
                    </div>
                    <div class="row pt-3">
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Category:</span>
                            {{this.rendered.category}}
                        </div>
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Language:</span>
                            {{this.rendered.language}}
                        </div>
                    </div>

                    <div class="row pt-3">
                        <div class="col-md-12 col-sm-12"><span class="text-bold">Astract:</span></div>
                        <div class="col-md-12 col-sm-12">
                            <div v-html="rendered.abstract"></div>
                        </div>
                    </div>

                    <div class="row pt-3">
                        <div class="col-md-12 col-sm-12"><span class="text-bold">Intro:</span></div>
                        <div class="col-md-12 col-sm-12">
                            <div v-html="rendered.intro"></div>
                        </div>
                    </div>

                    <div class="row pt-3" v-for="(field, index) in nameFields">
                        <div class="col-md-12 col-sm-12"><span class="text-bold">{{field.name}}:</span></div>
                        <div class="col-md-12 col-sm-12">
                            <div v-html="`${fields[index]}`"></div>
                        </div>
                    </div>

                    <div class="row pt-3">
                        <div class="col-md-12 col-sm-12"><span class="text-bold">Ending:</span></div>
                        <div class="col-md-12 col-sm-12">
                            <div v-html="rendered.ending"></div>
                        </div>
                    </div>


                    <div class="row pt-3">
                        <div class="col-md-12 col-sm-12"><span class="text-bold">Biography:</span></div>
                        <div class="col-md-12 col-sm-12">
                            <div v-html="rendered.biography"></div>
                        </div>
                    </div>

                    <div class="row pt-3">
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Tags: </span>{{rendered.tags}}</div>
                        <div class="col-md-6 col-sm-12"  v-show="true"><span class="text-bold">Donwload PDF:</span><a href="#" class="btn button" target="_blank">Download</a> </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>


    export default {
        name: "UserShow",
        props: ['item', 'role'],
        data: () => {
            return {
                rendered: {},
                authors: [],
                fields: [],
                nameFields:[]
            }
        },
        mounted() {
            this.rendered = JSON.parse(this.item);
            this.authors = JSON.parse(this.rendered.json_authors);
            this.nameFields = JSON.parse(this.rendered.template_fields);
            this.createFields();
            console.log(this.rendered);
            console.log(this.fields);
            console.log(this.role)
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


            }
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
