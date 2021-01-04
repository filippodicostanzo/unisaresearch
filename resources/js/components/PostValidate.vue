<template>
    <div>
        <div class="row">
            <div class="col-lg-12 margin-tb">

                <div class="card">
                    <div class="card-header">
                        <h1 class="m0 text-dark card-title text-xl">
                            {{this.rendered.title}}
                        </h1>
                    </div>

                    <div class="card-body no-padding">
                        <div class="row pt-3">
                            <div class="col-md-6 col-sm-12"><span class="text-bold">Template:</span>
                                {{this.rendered.template_fk.name}}
                            </div>
                            <div class="col-md-6 col-sm-12"><span class="text-bold">Category:</span>
                                {{this.rendered.category_fk.name}}
                            </div>
                        </div>

                        <div class="row pt-3" v-for="(field, index) in nameFields">
                            <div class="col-md-12 col-sm-12"><span class="text-bold">{{field.name}}:</span></div>
                            <div class="col-md-12 col-sm-12">
                                <div v-html="`${fields[index]}`"></div>
                            </div>
                        </div>

                        <div class="row pt-3">
                            <div class="col-md-6 col-sm-12"><span class="text-bold">Tags: </span>{{rendered.tags}}</div>
                            <div class="col-md-6 col-sm-12" v-show="true"><span class="text-bold">Download PDF:</span><a
                                :href="rendered.pdf" class="btn button" target="_blank">Download</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 margin-tb">

                <div class="card" v-for="review in json_reviews">
                    <div class="card-header">
                        <h1 class="m0 text-dark card-title text-xl">
                            Review by {{review.user_fk.name}} {{review.user_fk.surname}}
                        </h1>
                    </div>

                    <div class="card-body no-padding">
                        <form @submit.prevent="submit">

                            <div class="row pt-3">
                                <div class="col-md-6 col-sm-12"><span
                                    class="text-bold"> General quality of the paper:</span>

                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <fieldset class="rating">
                                        <input type="radio" id="f1_star5" v-model="review.field_1" value="5" disabled /><label
                                        class="full" for="f1_star5" title="Awesome - 5 stars"></label>
                                        <input type="radio" id="f1_star4" v-model="review.field_1" value="4" disabled/><label
                                        class="full" for="f1_star4" title="Pretty good - 4 stars"></label>
                                        <input type="radio" id="f1_star3" v-model="review.field_1" value="3" disabled/><label
                                        class="full" for="f1_star3" title="Meh - 3 stars"></label>
                                        <input type="radio" id="f1_star2" v-model="review.field_1" value="2" disabled/><label
                                        class="full" for="f1_star2" title="Kinda bad - 2 stars"></label>
                                        <input type="radio" id="f1_star1" v-model="review.field_1" value="1" disabled/><label
                                        class="full" for="f1_star1" title="Sucks big time - 1 star"></label>
                                    </fieldset>
                                </div>
                            </div>


                            <div class="row pt-3">
                                <div class="col-md-6 col-sm-12"><span
                                    class="text-bold"> General quality of the paper:</span>

                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <fieldset class="rating">
                                        <input type="radio" id="f2_star5" v-model="review.field_2" value="5" disabled/><label
                                        class="full" for="f2_star5" title="Awesome - 5 stars"></label>
                                        <input type="radio" id="f2_star4" v-model="review.field_2" value="4" disabled/><label
                                        class="full" for="f2_star4" title="Pretty good - 4 stars"></label>
                                        <input type="radio" id="f2_star3" v-model="review.field_2" value="3" disabled/><label
                                        class="full" for="f2_star3" title="Meh - 3 stars"></label>
                                        <input type="radio" id="f2_star2" v-model="review.field_2" value="2" disabled/><label
                                        class="full" for="f2_star2" title="Kinda bad - 2 stars"></label>
                                        <input type="radio" id="f2_star1" v-model="review.field_2" value="1" disabled/><label
                                        class="full" for="f2_star1" title="Sucks big time - 1 star"></label>

                                    </fieldset>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <div class="col-md-6 col-sm-12"><span class="text-bold"> Relevance of the paper:</span>

                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <fieldset class="rating">
                                        <input type="radio" id="f3_star5" v-model="review.field_3" value="5" disabled/><label
                                        class="full" for="f3_star5" title="Awesome - 5 stars"></label>
                                        <input type="radio" id="f3_star4" v-model="review.field_3" value="4" disabled/><label
                                        class="full" for="f3_star4" title="Pretty good - 4 stars"></label>
                                        <input type="radio" id="f3_star3" v-model="review.field_3" value="3" disabled/><label
                                        class="full" for="f3_star3" title="Meh - 3 stars"></label>
                                        <input type="radio" id="f3_star2" v-model="review.field_3" value="2" disabled/><label
                                        class="full" for="f3_star2" title="Kinda bad - 2 stars"></label>
                                        <input type="radio" id="f3_star1" v-model="review.field_3" value="1" disabled/><label
                                        class="full" for="f3_star1" title="Sucks big time - 1 star"></label>

                                    </fieldset>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <div class="col-md-12 col-xs-12 center">
                                    <div class="form-group">
                                        <label class="form__label">Review:</label>
                                        <div v-html="review.review"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row pt-3">
                                <div class="col-md-12 col-xs-12 center">
                                    <div class="form-group">
                                        <button class="button btn-primary btn btn-block" type="submit"
                                                :disabled="submitStatus === 'PENDING'"><i
                                            class="fa fa-floppy-o" aria-hidden="true"></i> Submit!
                                        </button>
                                        <p class="typo__p" v-if="submitStatus === 'OK'">Thanks for your submission!</p>
                                        <p class="typo__p" v-if="submitStatus === 'ERROR'">Please fill the form
                                            correctly.</p>
                                        <p class="typo__p" v-if="submitStatus === 'PENDING'">Sending...</p>

                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    export default {
        name: "PostValidate.vue",
        props: ['item', 'reviews', 'title'],
        data: () => {
            return {
                rendered: {
                    template_fk: {},
                    category_fk: {}
                },
                review: {
                    post: 0,
                    field_1: 0,
                    field_2: 0,
                    field_3: 0,
                    review: ''
                },
                json_reviews: [],
                nameFields: [],
                fields: [],
                submitStatus: null,
                editorConfig: {
                    filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
                    filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token=',
                    filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
                    filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token='
                }
            }
        },

        mounted() {

            this.json_reviews = JSON.parse(this.reviews);
            console.log(this.json_reviews);
            this.rendered = JSON.parse(this.item);

            this.nameFields = JSON.parse(this.rendered.template_fk.fields);

            this.createFields();
        },
        methods: {


            submit() {
                this.review.post = this.rendered.id;
                console.log(this.rendered);
                console.log(this.review);
                this.$http
                    .post("/admin/reviews", this.review)
                    .then(response => {
                        if (response.status === 200) {
                            window.location.href = route('posts.index')
                        }
                    })
                    .catch(error => {
                        this.errors = error.response.data.errors;
                    });
            },


            createFields() {
                if (this.rendered.fields_1 !== '') {
                    this.fields.push(this.rendered.field_1);
                }
                if (this.rendered.fields_2 !== '') {
                    this.fields.push(this.rendered.field_2);
                }
                if (this.rendered.fields_3 !== '') {
                    this.fields.push(this.rendered.field_3);
                }
                if (this.rendered.fields_4 !== '') {
                    this.fields.push(this.rendered.field_4);
                }
                if (this.rendered.fields_5 !== '') {
                    this.fields.push(this.rendered.field_5);
                }
                if (this.rendered.fields_6 !== '') {
                    this.fields.push(this.rendered.field_6);
                }
                if (this.rendered.fields_7 !== '') {
                    this.fields.push(this.rendered.field_7);
                }
                if (this.rendered.fields_8 !== '') {
                    this.fields.push(this.rendered.field_8);
                }
                if (this.rendered.fields_9 !== '') {
                    this.fields.push(this.rendered.field_9);
                }


            }
        },
    }
</script>

<style scoped>
    .rating {
        border: none;
        float: left;
    }

    .rating > input {
        display: none;
    }

    .rating > label:before {
        margin: 5px;
        font-size: 1.25em;
        font-family: "Font Awesome 5 Free";
        display: inline-block;
        content: "\f005";
    }

    .rating > label {
        color: #ddd;
        float: right;
    }

    /***** CSS Magic to Highlight Stars on Hover *****/

    .rating > input:checked ~ label, /* show gold star when clicked */
    .rating:not(:checked) > label:hover, /* hover current star */
    .rating:not(:checked) > label:hover ~ label {
        color: #FFD700;
    }

    /* hover previous stars in list */

    .rating > input:checked + label:hover, /* hover current star when changing rating */
    .rating > input:checked ~ label:hover,
    .rating > label:hover ~ input:checked ~ label, /* lighten current selection */
    .rating > input:checked ~ label:hover ~ label {
        color: #FFED85;
    }

</style>
