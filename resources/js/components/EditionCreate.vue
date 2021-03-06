<template>
    <div class="row vue-forms">
        <div class="col-lg-12 margin-tb">
            <div class="card card-mini">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        <span v-if="this.source === 'new'">Create New Edition</span>
                        <span v-if="this.source === 'edit'">Edit {{this.edition.name}} </span>
                    </h1>
                    <div class="card-action">
                        <a :href="route('editions.index')">
                            <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>

                    </div>
                </div>
                <div class="card-body">
                    <form @submit.prevent="submit">
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.edition.name.$error }">
                                    <label class="form__label">Name</label>
                                    <input class="form__input" v-model="$v.edition.name.$model"/>
                                </div>
                                <div class="error" v-if="!$v.edition.name.required">Name is required</div>
                                <div class="error" v-if="!$v.edition.name.minLength">Name must have at least
                                    {{$v.edition.name.$params.minLength.min}} letters.
                                </div>
                            </div>

                            <div class="col-md-6 col-xs-12">
                                <label class="form__label">Active</label>
                                <input type="checkbox" name="active"  v-model="edition.active">
                            </div>
                        </div>

                        <div class="row pt-3">

                            <div class="col-md-4 cols-sm-12">
                                <div class="form-group"  :class="{ 'form-group--error': $v.edition.start.$error }">
                                    <label class="form__label">Start</label>
                                    <input class="form__input" type="date" name="start" v-model="$v.edition.start.$model">
                                </div>
                                <div class="error" v-if="!$v.edition.start.required">Start Date is required</div>
                                <div class="error" v-if="!$v.edition.start.minValue">Start Date is incorrect</div>
                            </div>

                            <div class="col-md-4 cols-sm-12">
                                <div class="form-group"  :class="{ 'form-group--error': $v.edition.end.$error }">
                                    <label class="form__label">End</label>
                                    <input class="form__input" type="date" name="end" v-model="$v.edition.end.$model">
                                </div>
                                <div class="error" v-if="!$v.edition.end.required">End Date is required</div>
                                <div class="error" v-if="!$v.edition.end.isAfterDate">End Date is incorrect</div>
                            </div>

                            <div class="col-md-4 col-xs-12">
                                <label class="form__label">Public Calendar</label>
                                <input type="checkbox" name="calendar"  v-model="edition.calendar">
                            </div>
                        </div>

                        <div class="row padding">
                            <div class="col-md-12 col-xs-12 center">
                                <div class="form-group">
                                    <label class="form__label">Description</label>
                                    <ckeditor v-model="edition.description" :config="editorConfig"></ckeditor>
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

    const isAfterDate = (value, vm) => {
        return new Date(value).getTime() > new Date(vm.start).getTime();
    };

    export default {
        name: "EditionCreate",
        props: ['item','title'],
        mounted() {
            if (this.item) {
                this.edition = JSON.parse(this.item);
                this.source = 'edit'
            }
            else {
                this.source = 'new'
            }
        },

        data() {
            return {
                edition: {
                    name: '',
                    active: true,
                    start: '',
                    end: '',
                    calendar:false,
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
            edition: {
                name: {
                    required,
                    minLength: minLength(4)
                },
                start: {
                    required,
                    minValue: value => value > new Date().toISOString()
                },
                end: {
                    required,
                    isAfterDate
                },
            }
        },
        methods: {
            submit() {

                console.log('submit!')
                this.$v.$touch()
                if (this.$v.$invalid) {
                    this.submitStatus = 'ERROR'
                } else {
                    console.log(this.edition);
                    if (this.source === 'new') {
                        this.$http
                            .post("/admin/editions", this.edition)
                            .then(response => {

                                if (response.status === 200) {
                                    window.location.href = route('editions.index')
                                }
                            })
                            .catch(error => {
                                alert(error.message)
                            });
                    }

                    else {
                        this.$http
                            .patch("/admin/editions/"+this.edition.id, this.edition)
                            .then(response => {

                                if (response.status === 200) {
                                    window.location.href = route('editions.index')
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
