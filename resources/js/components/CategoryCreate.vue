<template>
    <div class="row vue-forms">
        <div class="col-lg-12 margin-tb">
            <div class="card card-mini">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        Create New Category
                    </h1>
                    <div class="card-action">
                        <a :href="route('categories.index')">
                            <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>

                    </div>
                </div>
                <div class="card-body">
                    <form @submit.prevent="submit">
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.category.name.$error }">
                                    <label class="form__label">Name</label>
                                    <input class="form__input" v-model="$v.category.name.$model"/>
                                </div>
                                <div class="error" v-if="!$v.category.name.required">Name is required</div>
                                <div class="error" v-if="!$v.category.name.minLength">Name must have at least
                                    {{$v.category.name.$params.minLength.min}} letters.
                                </div>
                            </div>

                            <div class="col-md-6 col-xs-12">
                                <label class="form__label">Visible</label>
                                <input type="checkbox" name="visible"  v-model="category.visible">
                            </div>

                        </div>

                        <div class="row padding">
                            <div class="col-md-12 col-xs-12 center">
                                <div class="form-group">
                                    <label class="form__label">Description</label>
                                    <ckeditor v-model="category.description" :config="editorConfig"></ckeditor>
                                </div>
                            </div>
                        </div>


                        <div class="row padding">
                            <div class="col-md-12 col-xs-12 center">
                                <div class="form-group">
                                    <button class="button btn-primary btn btn-block" type="submit" :disabled="submitStatus === 'PENDING'"><i
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
</template>

<script>

    import {minLength, required} from 'vuelidate/lib/validators'

    export default {
        name: "CategoryCreate",
        props: ['item'],
        mounted() {
            if (this.item) {
                this.category = JSON.parse(this.item);
                this.source = 'edit'
            }
            else {
                this.source = 'new'
            }
        },

        data() {
            return {
                    category: {
                        name: '',
                        visible: true,
                        description: '',
                    },
                source: '',
                submitStatus: null,
                editorConfig: {
                    filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
                    filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token=',
                    filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
                    filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token='
                }

            }
        },
        validations: {
            category: {
                name: {
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
                    console.log(this.category);
                    if (this.source === 'new') {
                        this.$http
                            .post("/admin/categories", this.category)
                            .then(response => {

                                if (response.status === 200) {
                                    window.location.href = route('categories.index')
                                }
                            })
                            .catch(error => {
                                alert(error.message)
                            });
                    }

                    else {
                        this.$http
                            .patch("/admin/categories/"+this.category.id, this.category)
                            .then(response => {

                                if (response.status === 200) {
                                    window.location.href = route('categories.index')
                                }
                            })
                            .catch(error => {
                                alert(error.message)
                            });

                    }

                    // do your submit logic here
                    this.submitStatus = 'PENDING'
                    setTimeout(() => {
                        this.submitStatus = 'OK'
                    }, 500)
                }
            }
        }
    }
</script>

<style scoped>


</style>
