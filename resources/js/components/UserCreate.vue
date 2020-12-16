<template>
    <div class="row vue-forms">
        <div class="col-lg-12 margin-tb">
            <div class="card card-mini">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl" v-show="this.source==='new'">
                        Create New User
                    </h1>

                    <h1 class="m0 text-dark card-title text-xl" v-show="this.source==='edit' && !this.profile">
                        Edit User
                    </h1>

                    <h1 class="m0 text-dark card-title text-xl" v-show="this.profile">
                        Edit Profile
                    </h1>

                    <div class="card-action">
                        <a :href="route('users.index')" v-show="!this.profile">
                            <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    <form @submit.prevent="submit" method="post">

                        <div class="row" v-if="!profile">
                            <div class="col-md-12 col-xs-12">
                                <div class="form-group">
                                    <select name="role" @change="selectChange($event)" v-model="key"
                                            class="form-control">
                                        <option value="">Choose Type Of User</option>
                                        <option v-for="role in this.json_roles" :value="role.id" :data-name="role.name"
                                                :id="'role_'+role.id">{{role.display_name}}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.user.name.$error }">
                                    <label class="form__label">First Name</label>
                                    <input class="form__input" v-model="$v.user.name.$model"/>
                                </div>
                                <div class="error" v-if="!$v.user.name.required">First Name is required</div>
                                <div class="error" v-if="!$v.user.name.minLength">First Name must have at least
                                    {{$v.user.name.$params.minLength.min}} letters.
                                </div>
                            </div>

                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.user.surname.$error }">
                                    <label class="form__label">Last Name</label>
                                    <input class="form__input" v-model="$v.user.surname.$model"/>
                                </div>
                                <div class="error" v-if="!$v.user.surname.required">Last Name is required</div>
                                <div class="error" v-if="!$v.user.surname.minLength">Last Name must have at least
                                    {{$v.user.surname.$params.minLength.min}} letters.
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.user.email.$error }">
                                    <label class="form__label">Email</label>
                                    <input class="form__input" v-model="$v.user.email.$model"
                                           :disabled="source === 'edit'"/>
                                </div>
                                <div class="error" v-if="!$v.user.email.required">Email is required</div>
                                <div class="error" v-if="!$v.user.email.minLength">Email must have at least
                                    {{$v.user.email.$params.minLength.min}} letters.
                                </div>
                                <div class="error" v-if="!$v.user.email.email">Email is not correct</div>
                            </div>

                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.user.password.$error }">
                                    <label class="form__label">Password</label>
                                    <input type="password" class="form__input" v-model="$v.user.password.$model"/>
                                </div>
                                <div class="error" v-if="!$v.user.password.required">Password is required</div>
                                <div class="error" v-if="!$v.user.password.minLength">Password must have at
                                    least
                                    {{$v.user.password.$params.minLength.min}} letters.
                                </div>
                            </div>

                        </div>

                        <div class="row"
                             v-if="active_role === 'researcher' || active_role==='user' || active_role==='supervisor'">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.user.title.$error }">
                                    <label class="form__label">Title</label>
                                    <select name="role" v-model="user.title"
                                            class="form-control">
                                        <option v-for="title in this.titles" :value="title.id">{{title.name}}</option>
                                    </select>
                                </div>
                                <div class="error" v-if="!$v.user.title.required">Title is required</div>
                            </div>

                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.user.gender.$error }">
                                    <label class="form__label">Gender</label>
                                    <select name="role" v-model="user.gender"
                                            class="form-control">
                                        <option v-for="gender in this.genders" :value="gender.id">{{gender.name}}
                                        </option>
                                    </select>
                                </div>
                                <div class="error" v-if="!$v.user.gender.required">Gender is required</div>
                            </div>
                        </div>

                        <div class="row"
                             v-if="active_role === 'researcher' || active_role==='user' || active_role==='supervisor'">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.user.country.$error }">
                                    <label class="form__label">Country</label>
                                    <select name="role" v-model="user.country"
                                            class="form-control">
                                        <option value="">Choose Country</option>
                                        <option v-for="(country, index) in this.json_countries" :value="index">
                                            {{country}}
                                        </option>
                                    </select>
                                </div>
                                <div class="error" v-if="!$v.user.country.required">Country is required</div>
                            </div>

                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.user.city.$error }">
                                    <label class="form__label">City</label>
                                    <input class="form__input" v-model="$v.user.city.$model"/>
                                </div>
                                <div class="error" v-if="!$v.user.city.required">City is required</div>
                                <div class="error" v-if="!$v.user.city.minLength">City must have at
                                    least
                                    {{$v.user.city.$params.minLength.min}} letters.
                                </div>
                            </div>

                        </div>

                        <div class="row"
                             v-if="active_role === 'researcher' || active_role==='user' || active_role==='supervisor'">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.user.affiliation.$error }">
                                    <label class="form__label">Affiliation</label>
                                    <input class="form__input" v-model="$v.user.affiliation.$model"/>
                                </div>
                                <div class="error" v-if="!$v.user.affiliation.required">Affiliation is required</div>
                                <div class="error" v-if="!$v.user.affiliation.minLength">Affiliation must have at
                                    least
                                    {{$v.user.affiliation.$params.minLength.min}} letters.
                                </div>
                            </div>

                            <div class="col-md-6 col-xs-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.user.disciplinary.$error }">
                                    <label class="form__label">Disciplinary</label>
                                    <input class="form__input" v-model="$v.user.disciplinary.$model"/>
                                </div>
                                <div class="error" v-if="!$v.user.affiliation.required">Disciplinary is required</div>
                                <div class="error" v-if="!$v.user.disciplinary.minLength">Disciplinary must have at
                                    least
                                    {{$v.user.disciplinary.$params.minLength.min}} letters.
                                </div>
                            </div>

                        </div>

                        <div class="row"
                             v-if="(active_role === 'researcher' || active_role==='user') && this.profile">
                            <div class="col-md-12 col-xs-12">
                                <div class="form-group">
                                    <label class="form__label">Curriculum Vitae</label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <a id="lfm_pdf" data-input="lfm_pdf_input" data-preview="lfm_pdf_preview"
                                               class="btn btn-secondary" v-on:click="openFileManager($event)">
                                                <i class="fa fa-picture-o"></i> Choose
                                            </a>
                                        </div>
                                        <input type="text" class="form__input form-control" id="lfm_pdf_input"
                                               ref="curriculumvitae"
                                               v-model="user.curriculumvitae"/>
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
                                    <p class="typo__p" v-if="submitStatus === 'OK'">Thanks for your submission! Waiting
                                        for Redirect</p>
                                    <p class="typo__p" v-if="submitStatus === 'ERROR'">Please fill the form
                                        correctly.</p>
                                    <p class="typo__p" v-if="submitStatus === 'PENDING'">Sending...</p>

                                    <p class="typo__p" v-if="submitStatus === 'SAVED'">Data Saved!</p>

                                </div>
                            </div>
                        </div>

                        <input type="hidden" v-model="user.role"/>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import {email, minLength, required} from 'vuelidate/lib/validators'

    export default {
        name: "UserCreate",
        props: ['item', 'roles', 'countries', 'role', 'profile', 'password'],
        mounted() {
            this.json_countries = JSON.parse(this.countries)
            this.json_roles = JSON.parse(this.roles);


            if (this.item) {
                console.log(this.item);
                this.user = JSON.parse(this.item);
                this.json_role = JSON.parse(this.role);
                this.key = this.json_role.id;
                this.active_role = this.json_role.name;
                this.user.password = this.password;
                console.log(this.active_role);
                this.source = 'edit'
            } else {
                this.source = 'new'
            }
        },

        data() {
            return {
                user: {
                    name: '',
                    surname: '',
                    email: '',
                    password: '',
                    title: '',
                    gender: '',
                    country: '',
                    city: '',
                    affiliation: '',
                    disciplinary: '',
                    role: '',
                    curriculumvitae: ''
                },
                titles: [
                    {id: '', name: 'Choose Title'},
                    {id: 'Prof', name: 'Prof.'},
                    {id: 'Dr', name: 'Dr.'},
                    {id: 'Mr', name: 'Mr.'},
                    {id: 'Ms', name: 'Ms.'},
                    {id: 'Other', name: 'Other'},
                ],
                genders: [
                    {id: '', name: 'Choose Gender'},
                    {id: 'M', name: 'Male'},
                    {id: 'F', name: 'Female'},
                ],

                source: '',
                submitStatus: null,
                editorConfig: {
                    filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
                    filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token=',
                    filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
                    filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token='
                },
                json_roles: [],
                json_countries: [],
                key: '',
                active_role: ''
            }
        },
        validations() {

            const admin_validate = {
                user: {
                    name: {
                        required,
                        minLength: minLength(4)
                    },
                    surname: {
                        required,
                        minLength: minLength(4)
                    },
                    email: {
                        required,
                        minLength: minLength(4),
                        email,
                    },
                    password: {
                        required,
                        minLength: minLength(8)
                    },

                },
                key: {
                    required
                }
            };
            const supervisor_validate = {
                user: {
                    name: {
                        required,
                        minLength: minLength(4)
                    },
                    surname: {
                        required,
                        minLength: minLength(4)
                    },
                    email: {
                        required,
                        minLength: minLength(4),
                        email,
                    },
                    password: {
                        required,
                        minLength: minLength(8)
                    },
                    title: {
                        required
                    },
                    gender: {
                        required
                    },
                    country: {
                        required
                    },
                    city: {
                        required,
                        minLength: minLength(3),
                    },
                    affiliation: {
                        required,
                        minLength: minLength(4),
                    },
                    disciplinary: {
                        required,
                        minLength: minLength(4),
                    }

                },
                key: {
                    required
                }
            };
            const researcher_validate = {};
            const user_validate = {
                user: {
                    name: {
                        required,
                        minLength: minLength(4)
                    },
                    surname: {
                        required,
                        minLength: minLength(4)
                    },
                    email: {
                        required,
                        minLength: minLength(4),
                        email,
                    },
                    password: {
                        required,
                        minLength: minLength(8)
                    },
                    title: {
                        required
                    },
                    gender: {
                        required
                    },
                    country: {
                        required
                    },
                    city: {
                        required,
                        minLength: minLength(3),
                    },
                    affiliation: {
                        required,
                        minLength: minLength(4),
                    },
                    disciplinary: {
                        required,
                        minLength: minLength(4),
                    }

                },
                key: {
                    required
                }
            };

            if (this.active_role === 'superadministrator' || this.active_role === 'administrator') {
                return admin_validate
            } else if (this.active_role === 'supervisor') {

                return supervisor_validate;
            } else if (this.active_role === 'researcher') {
                return user_validate
            } else {
                return user_validate
            }
        },
        methods: {
            submit() {


                console.log('submit!')
                this.$v.$touch()
                console.log(this.key);

                if (this.$v.$invalid) {
                    this.submitStatus = 'ERROR'
                } else {
                    console.log(this.user);
                    console.log(this.source);
                    console.log(this.role);

                    if (this.source === 'new') {
                        this.$http
                            .post("/admin/users", this.user)
                            .then(response => {

                                if (response.status === 200) {
                                    window.location.href = route('users.index', {type: this.active_role})
                                }
                            })
                            .catch(error => {
                                alert(error.message)
                            });
                    }

                    if (this.source === 'edit') {

                        /**
                         *
                         * If Edit Profile
                         */

                        if (this.profile) {


                            this.user.new_password = this.password !== this.user.password;
                            if (this.$refs.curriculumvitae) {
                                this.user.curriculumvitae = this.$refs.curriculumvitae.value;
                            }
                            console.log(this.user);

                            let url = '';
                            let json_role = this.role;


                            console.log(this.role);

                            console.log(json_role);

                            if (this.json_role.name === 'superadministrator' || this.json_role.name === 'administrator') {

                                url = '/admin/users/'
                            } else {
                                url = '/profile/'
                            }

                            this.$http
                                .patch(url + this.user.id, this.user)
                                .then(response => {

                                    console.log(this.user.curriculumvitae);


                                    if (response.status === 200) {
                                        this.submitStatus = 'SAVED';
                                        return
                                    }

                                })
                                .catch(error => {
                                    alert(error.message)
                                });
                        }

                        /**
                         *
                         * If Edit User
                         *
                         */

                        else {
                            this.user.role = this.key;
                            this.user.new_password = this.password !== this.user.password;
                            console.log(this.user);
                            this.$http
                                .patch("/admin/users/" + this.user.id, this.user)
                                .then(response => {


                                    if (response.status === 200) {
                                        window.location.href = route('users.index', {type: this.active_role})
                                    }


                                })
                                .catch(error => {
                                    alert(error.message)
                                });

                        }
                    }


// do your submit logic here
                    this.submitStatus = 'PENDING'
                    /*     setTimeout(() => {
                             this.submitStatus = 'OK'
                         }, 500)*/
                }
            },

            selectChange(e) {
                e.preventDefault();
                if (e.target.value) {
                    const active = document.querySelector('#role_' + this.key);
                    this.active_role = active.dataset.name;
                    this.user.role = e.target.value;
                } else {
                    this.active_role = ''
                }

                console.log(this.active_role);
            },

            openFileManager(e) {

                e.preventDefault();
                window.open(`/laravel-filemanager` + '?type=file', 'FileManager', 'width=900,height=600');
                //window.open(`/laravel-filemanager`, 'targetWindow', 'width=900,height=600')
                var self = this
                let curriculumvitae = '';
                window.SetUrl = function (items) {
                    console.log('B');
                    console.log('C')
                    console.log(items);
                    var input = document.getElementById('lfm_pdf_input');
                    input.value = items[0].url
                    //self.form.main_image = items[0].url
                }


                console.log(this.user);

                return false;

            },

        }


    }
</script>
<style scoped>


</style>
