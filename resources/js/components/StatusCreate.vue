<template>
    <div class="row vue-forms">
        <div class="col-lg-12 margin-tb">
            <div class="card card-mini">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        Create New Status
                    </h1>
                    <div class="card-action">
                        <a :href="route('statuses.index')">
                            <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>

                    </div>
                </div>
                <div class="card-body">
                    <form @submit.prevent="submit">
                        <div class="row">
                            <div class="col-md-4 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.status.name.$error }">
                                    <label class="form__label">Name</label>
                                    <input class="form__input" v-model="$v.status.name.$model"/>
                                </div>
                                <div class="error" v-if="!$v.status.name.required">Name is required</div>
                                <div class="error" v-if="!$v.status.name.minLength">Name must have at least
                                    {{$v.status.name.$params.minLength.min}} letters.
                                </div>
                            </div>

                            <div class="col-md-4 col-xs-12">
                                <label class="form__label">Visible</label>
                                <input type="checkbox" name="visible" v-model="status.visible">
                            </div>

                            <div class="col-md-4 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.status.color.$error }">
                                    <label class="form__label">Color</label>
                                    <input type="color" name="picker" v-model="status.color">
                                    <input type="text" name="color" class="form__input color__input"
                                           v-model="status.color">
                                </div>
                                <div class="error" v-if="!$v.status.color.required">Color is required</div>
                            </div>

                        </div>


                        <div class="row padding">
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
</template>

<script>

    import {minLength, required} from 'vuelidate/lib/validators'

    export default {
        name: "StatusCreate",
        props: ['item'],
        mounted() {
            console.log(this.item);
            if (this.item) {
                this.status = JSON.parse(this.item);
                this.source = 'edit'
            } else {
                this.source = 'new'
            }

            console.log(this.source);
        },

        data() {
            return {
                status: {
                    name: '',
                    visible: true,
                    color: '',
                },
                source: '',
                submitStatus: null,

            }
        },
        validations: {
            status: {
                name: {
                    required,
                    minLength: minLength(4)
                },
                color: {
                    required,
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
                    console.log(this.status);
                    if (this.source === 'new') {
                        this.$http
                            .post("/admin/statuses", this.status)
                            .then(response => {

                                if (response.status === 200) {
                                    window.location.href = route('statuses.index')
                                }

                            })
                            .catch(error => {
                                alert(error.message)
                            });
                    } else {
                        this.$http
                            .patch("/admin/statuses/" + this.status.id, this.status)
                            .then(response => {

                                if (response.status === 200) {
                                    window.location.href = route('statuses.index')
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

    input[type=color] {
        width: 40px;
        padding: 0;
        height: 40px;
    }

    .color__input {
        width: calc(100% - 44px);
        vertical-align: top;
    }

</style>
