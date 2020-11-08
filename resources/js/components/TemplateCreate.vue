<template>
    <div class="row vue-forms">
        <div class="col-lg-12 margin-tb">
            <div class="card card-mini">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        Create New Template
                    </h1>
                    <div class="card-action">
                        <a :href="route('templates.index')">
                            <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>

                    </div>
                </div>
                <div class="card-body">
                    <form @submit.prevent="submit">
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.template.name.$error }">
                                    <label class="form__label">Name</label>
                                    <input class="form__input" v-model="$v.template.name.$model"/>
                                </div>
                                <div class="error" v-if="!$v.template.name.required">Name is required</div>
                                <div class="error" v-if="!$v.template.name.minLength">Name must have at least
                                    {{$v.template.name.$params.minLength.min}} letters.
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <label class="form__label">Visible</label>
                                <input type="checkbox" name="visible" v-model="template.active">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <div class="form-group">
                                    <label class="form__label">Insert Fields</label>
                                    <div class="form-group" v-for="(input,k) in template.fields" :key="k"
                                         :class="{ 'form-group--error': $v.template.fields.$error }">
                                        <input type="text" class="form__input" v-model="input.name"/>
                                        <span class="remove">
                                            <i class="fas fa-minus-circle" @click="remove(k)"
                                            v-show="k || ( !k && template.fields.length > 1)"></i>
                                        </span>
                                        <span class="add">
                                            <i class="fas fa-plus-circle" @click="add(k)"
                                                  v-show="k == template.fields.length-1"></i>
                                        </span>
                                    </div>
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
        name: "TemplateCreate",
        props: ['item'],
        data() {
            return {
                template: {
                    name: '',
                    active: false,
                    fields: [
                        {
                            name: "",
                        }
                    ]
                },
                source: '',
                submitStatus: null,
            };
        },
        mounted() {
            if (this.item) {
                this.template = JSON.parse(this.item);
                this.template.fields = JSON.parse(this.template.fields);
                this.source = 'edit'
            } else {
                this.source = 'new'
            }

        },
        validations: {
            template: {
                name: {
                    required,
                    minLength: minLength(4)
                },
                fields: {
                    required,
                }
            },
        },
        methods: {
            add(index) {
                if (this.template.fields.length<10) {
                    this.template.fields.push({name: ""});
                }
                else {
                    window.alert('Max Fields');
                }
            },
            remove(index) {
                this.template.fields.splice(index, 1);
            },

            submit() {

                console.log('submit!')
                this.$v.$touch()
                if (this.$v.$invalid || this.template.fields[0].name === '') {
                    this.submitStatus = 'ERROR'
                } else {
                    console.log(this.template);
                    if (this.source === 'new') {
                        this.$http
                            .post("/admin/templates", this.template)
                            .then(response => {

                                if (response.status === 200) {
                                    window.location.href = route('templates.index')
                                }
                            })
                            .catch(error => {
                                alert(error.message)
                            });
                    } else {
                        console.log(this.template);
                        this.$http
                            .patch("/admin/templates/" + this.template.id, this.template)
                            .then(response => {

                                if (response.status === 200) {
                                    window.location.href = route('templates.index')
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
            },

            addCandidate() {
                console.log('ALBA');
                console.log(this.template);
                this.$http
                    .post("/admin/templates", this.template)
                    .then(response => {

                        if (response.status == 200) {
                            window.location.href = '/admin/templates'
                        }
                    })
                    .catch(error => {
                        alert(error.message)
                    });
            }
        }
    }
</script>

<style scoped lang="scss">
    .add {
        cursor: pointer;
        i {
            color: #143059;
            font-size: 2rem;
            padding: 10px;
        }
    }
    .remove {
        cursor: pointer;
        i {
            color: #dc3545;
            font-size: 2rem;
            padding: 10px;
        }
    }
</style>
