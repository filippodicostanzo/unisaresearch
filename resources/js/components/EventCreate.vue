<template>
    <div class="row vue-forms">

        <confirm-dialog v-if="modal" @dialogbox="getDialog"></confirm-dialog>

        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        <span v-if="this.source === 'new'">Create New Event</span>
                        <span v-if="this.source === 'edit'">Edit {{this.evt.title}} </span>
                    </h1>
                    <div class="card-action">
                        <a :href="route('events.index')">
                            <i class="fas fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>

                    </div>
                </div>


                <div class="card-body no-padding">
                    <form @submit.prevent="submit">
                        <div class="row pt-3">
                            <div class="col-md-12 col-sm-12"><span class="text-bold">Type Of Event:</span></div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group radio-button">
                                    <input type="radio" v-model="evt.type" value="paper"
                                           v-on:change="checkRadio($event)">
                                    <label class="form__label">Paper Presentation</label>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">

                                <div class="form-group radio-button">
                                    <input type="radio" v-model="evt.type" value="break"
                                           v-on:change="checkRadio($event)">
                                    <label class="form__label">Break</label>
                                </div>

                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group radio-button">
                                    <input type="radio" v-model="evt.type" value="other"
                                           v-on:change="checkRadio($event)">
                                    <label class="form__label">Other</label>
                                </div>
                            </div>
                        </div>


                        <div class="row pt-3" v-if="evt.type=='paper'">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label class="form__label">Choose Paper</label>
                                    <select @change="onChangePaper($event)">
                                        <option>Choose</option>
                                        <option v-for="post in this.rendered_posts" :value="post.id">{{post.title}}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-md-6 cols-sm-12">
                                <div class="form-group" :class="{ 'form-group--error': $v.evt.title.$error }">
                                    <label class="form__label">Title</label>
                                    <input class="form__input" name="title" v-model="$v.evt.title.$model"/>
                                </div>
                                <div class="error" v-if="!$v.evt.title.required">Title is required</div>
                                <div class="error" v-if="!$v.evt.title.minLength">Title must have at least
                                    {{$v.evt.title.$params.minLength.min}} letters.
                                </div>

                            </div>
                            <div class="col-md-6 cols-sm-12">
                                <label class="form__label">Authors</label>
                                <input class="form__input" name="authors" v-model="evt.authors">
                            </div>
                        </div>

                        <div class="row pt-3">
                            <div class="col-md-6 cols-sm-12">
                                <div class="form-group"  :class="{ 'form-group--error': $v.evt.start.$error }">
                                    <label class="form__label">Start</label>
                                    <input class="form__input" type="datetime-local" name="start" v-model="$v.evt.start.$model">
                                </div>
                                <div class="error" v-if="!$v.evt.start.required">Start Date is required</div>
                            </div>
                            <div class="col-md-6 cols-sm-12">
                                <div class="form-group"  :class="{ 'form-group--error': $v.evt.end.$error }">
                                    <label class="form__label">End</label>
                                    <input class="form__input" type="datetime-local" name="end" v-model="$v.evt.end.$model">
                                </div>
                                <div class="error" v-if="!$v.evt.end.required">End Date is required</div>
                            </div>
                        </div>

                        <div class="row pt-3">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label class="form__label">Choose Room</label>
                                    <select name="rooms" v-model="evt.room">
                                        <option v-for="room in this.rendered_rooms" :value="room.id">{{room.name}}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label class="form__label">Active</label>
                                    <input type="checkbox" name="visible" v-model="evt.active">
                                </div>
                            </div>
                        </div>

                        <div class="row pt-3">
                            <div class="col-md-12 col-xs-12 center">
                                <div class="form-group">
                                    <label class="form__label">Description</label>
                                    <ckeditor v-model="evt.description" :config="editorConfig"></ckeditor>
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
                                    <p class="typo__p" v-if="submitStatus === 'BUSY'">The Date And Time are Busy.</p>
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

    import {minLength, required, minValue } from 'vuelidate/lib/validators';
    import ConfirmDialog from "./ConfirmDialog";

    export default {
        name: "EventCreate.vue",
        components: {'confirm-dialog':ConfirmDialog},
        props: ['item', 'title', 'rooms', 'posts','events'],

        data: () => {
            return {
                evt: {
                    type: 'paper',
                    title: '',
                    authors: '',
                    room: 1,
                    start: '',
                    end: '',
                    active:true,
                    description: ''
                },
                rendered_posts: [],
                rendered_rooms: [],
                source: '',
                submitStatus: null,
                modal:false,
                editorConfig: {
                    filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
                    filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token=',
                    filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
                    filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token='
                },



            }
        },

        validations: {
            evt: {
                title: {
                    required,
                    minLength: minLength(4)
                },
                start: {
                    required,
                    minValue: value => value > new Date().toISOString()
                },
                end: {
                    required,
                    minValue: value => value > new Date().toISOString()
                }
            },
        },


        mounted() {

            this.rendered_posts = JSON.parse(this.posts);
            this.rendered_rooms = JSON.parse(this.rooms);
            this.rendered_events = JSON.parse(this.events);
            console.log(this.rendered_events);

            if (this.item) {
                this.evt = JSON.parse(this.item);
                this.source = 'edit'
            } else {
                this.source = 'new'
            }


        },
        methods: {


            onChangePaper(event) {
                console.log(event.target.value);
                let paper = this.rendered_posts.find(item => item.id == event.target.value);


                if (paper && this.evt.type == 'paper') {

                    let authors = paper.user_fk.name + ' ' + paper.user_fk.surname;
                    if (paper.authors.length > 0) {
                        authors += ' - ';
                        paper.authors.map((item, index) => {
                            authors += item.firstname + ' ' + item.lastname

                            if (index !== paper.authors.length - 1) {
                                authors += ' - ';
                            }

                        });
                    }

                    this.evt.authors = authors;
                    this.evt.title = paper.title;


                } else {
                    this.evt.title = '';
                    this.evt.authors = '';
                }
                console.log(paper);
            },

            checkRadio(event) {
                console.log(event.target.value);
                if (event.target.value !== 'paper') {
                    this.evt.title = '';
                    this.evt.authors = '';
                }
            },

            storeData() {
                if (this.source === 'new') {
                    this.$http
                        .post("/admin/events", this.evt)
                        .then(response => {

                            console.log(response);

                            if (response.status === 200) {

                                if(response.data.isValid == false){
                                    console.log(response.data.errors);
                                    this.submitStatus = 'BUSY'
                                }

                                else {
                                    this.submitStatus = 'OK'

                                    window.location.href = route('events.index')
                                }

                            }

                        })
                        .catch(error => {
                            alert(error.message)
                        });
                } else {
                    this.$http
                        .patch("/admin/events/" + this.evt.id, this.evt)
                        .then(response => {

                            if (response.status === 200) {
                                if(response.data.isValid == false){
                                    console.log(response.data.errors);
                                    this.submitStatus = 'BUSY'
                                }

                                else {
                                    this.submitStatus = 'OK'

                                    window.location.href = route('events.index')
                                }
                            }


                        })
                        .catch(error => {
                            alert(error.message)
                        });

                }
            },


            getDialog(value) {
                if (value==="confirm") {
                    this.storeData();
                }
                else {
                    this.submitStatus = null;
                }
                this.modal=false;
            },

            submit() {

                this.submitStatus = 'PENDING'
              /*  setTimeout(() => {
                    this.submitStatus = 'OK'
                }, 500)*/


                this.$v.$touch()
                if (this.$v.$invalid) {
                    this.submitStatus = 'ERROR'
                } else {

                    /*
                    *
                    * Rimuovo dall'insieme l'evento solo per l'edit
                    * */


                    if (this.item) {

                        this.rendered_events.splice(this.rendered_events.findIndex(e => e.id === this.evt.id), 1);
                    }


                    if(this.rendered_events.some(event => event.title === this.evt.title)){
                        this.modal=true;

                    } else{
                        this.storeData();
                    }


                }

            }
        }

    }
</script>

<style scoped lang="scss">


    select {
        padding: 10px;
        width: 100%;
        font-size: 17px;
        font-family: Raleway;
        border: 1px solid #aaaaaa;
    }

    .radio-button {
        display: flex;
        align-items: center;
        padding: 10px 0;
        justify-content: center;

        label {
            margin-bottom: 0;
            margin-left: 10px;
        }

        input[type="radio"] {
            width: 20px;
            height: 20px;
        }
    }

</style>
