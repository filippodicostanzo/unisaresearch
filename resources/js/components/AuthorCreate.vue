<template>
    <div class="row vue-forms">
        <div class="col-lg-12 margin-tb">
            <div class="card card-mini">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        Create New Author
                    </h1>
                    <div class="card-action">
                        <a :href="route('authors.index')">
                            <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    <form @submit.prevent="submit">
                        <div class="row">
                            <div class="col-md-4 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.author.firstname.$error }">
                                    <label class="form__label">First Name</label>
                                    <input class="form__input" v-model="$v.author.firstname.$model"/>
                                </div>
                                <div class="error" v-if="!$v.author.firstname.required">First Name is required</div>
                                <div class="error" v-if="!$v.author.firstname.minLength">First Name must have at least
                                    {{$v.author.firstname.$params.minLength.min}} letters.
                                </div>
                            </div>

                            <div class="col-md-4 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.author.lastname.$error }">
                                    <label class="form__label">Last Name</label>
                                    <input class="form__input" v-model="$v.author.lastname.$model"/>
                                </div>
                                <div class="error" v-if="!$v.author.lastname.required">Last Name is required</div>
                                <div class="error" v-if="!$v.author.lastname.minLength">Last Name must have at least
                                    {{$v.author.lastname.$params.minLength.min}} letters.
                                </div>
                            </div>

                            <div class="col-md-4 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.author.email.$error }">
                                    <label class="form__label">Email</label>
                                    <input class="form__input" v-model="$v.author.email.$model" :disabled="this.source ==='edit'" />
                                </div>
                                <div class="error" v-if="!$v.author.email.required">Email is required</div>
                                <div class="error" v-if="!$v.author.email.minLength">Email must have at least
                                    {{$v.author.email.$params.minLength.min}} letters.
                                </div>
                            </div>

                        </div>


                        <div class="row padding">
                            <div class="col-md-12 col-xs-12 center">
                                <div class="form-group">
                                    <button class="button btn-primary btn btn-block" type="submit"
                                            :disabled="submitStatus === 'PENDING'"><i
                                        class="fa fa-floppy-o" aria-hidden="true"></i> Submit!
                                    </button>
                                    <p class="typo__p" v-if="submitStatus === 'OK'">Thanks for your submission! Waiting
                                        for Redirect</p>
                                    <p class="typo__p" v-if="submitStatus === 'ERROR'">ERROR! the author is already
                                        present in the database</p>
                                    <p class="typo__p" v-if="submitStatus === 'PENDING'">Sending...</p>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import {minLength, required, email} from 'vuelidate/lib/validators'

    export default {
        name: "AuthorCreate",
        props: ['item'],
        mounted() {
            if (this.item) {
                this.author = JSON.parse(this.item);
                this.source = 'edit'
            } else {
                this.source = 'new'
            }
            console.log(route('authors.index'));
        },

        data() {
            return {
                author: {
                    firstname: '',
                    lastname: '',
                    email: '',
                    affiliation: ''
                },
                source: '',
                submitStatus: null,
                editorConfig: {
                    filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
                    filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token=',
                    filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
                    filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token='
                },
                errors: null

            }
        },
        validations: {
            author: {
                firstname: {
                    required,
                    minLength: minLength(4)
                },
                lastname: {
                    required,
                    minLength: minLength(4)
                },
                email: {
                    required,
                    minLength: minLength(4)
                }
            },
        },
        methods: {
            submit() {

                console.log('submit!')
                this.$v.$touch()
                if (this.$v.$invalid) {
                    this.submitStatus = 'ERROR'
                } else {
                    console.log(this.author);
                    if (this.source === 'new') {
                        this.$http
                            .post("/admin/authors", this.author)
                            .then(response => {
                                if (response.status === 200) {
                                    window.location.href = route('authors.index')
                                }
                            })
                            .catch(error => {
                                this.errors = error.response.data.errors;
                            });
                    } else {
                        this.$http
                            .patch("/admin/authors/" + this.author.id, this.author)
                            .then(response => {
                                if (response.status === 200) {

                                    window.location.href = route('authors.index')
                                }

                            })
                            .catch(error => {
                                this.errors = error.response.data.errors;
                            });

                    }

// do your submit logic here
                    this.submitStatus = 'PENDING'
                    setTimeout(() => {
                        console.log('AAAABBBCCC');
                        console.log(this.errors);
                        if (this.errors) {
                            this.submitStatus = 'ERROR'
                        } else {
                            this.submitStatus = 'OK'
                        }
                    }, 500)
                }
            }
        }
    }
</script>
<style scoped>

</style>
