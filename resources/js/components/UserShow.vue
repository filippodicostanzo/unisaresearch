<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.rendered.name}} {{this.rendered.surname}} <span>{{this.role}}</span>
                    </h1>

                    <div class="card-action">
                        <a :href="route('users.index', {type: this.role})">
                            <i class="fa fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>

                </div>

                <div class="card-body no-padding">
                    <div class="row pt-3">
                        <div class="col-md-6 col-sm-12"><span class="text-bold">First Name</span>  {{this.rendered.name}}</div>
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Last Name:</span> {{this.rendered.surname}}</div>
                    </div>
                    <div class="row pt-3">
                        <div class="col-md-6 col-sm-12"><span class="text-bold">Email:</span>  {{this.rendered.email}}</div>
                        <div class="col-md-6 col-sm-12"><span class="text-bold" v-show="this.rendered.title">Title:</span> {{this.rendered.title}}</div>
                    </div>
                    <div class="row pt-3">
                        <div class="col-md-6 col-sm-12" v-show="this.rendered.gender"><span class="text-bold" >Gender:</span>  {{this.rendered.gender}}</div>
                        <div class="col-md-6 col-sm-12" v-show="this.rendered.country"><span class="text-bold" >Country:</span> {{this.rendered.country}}</div>
                    </div>

                    <div class="row pt-3">
                        <div class="col-md-6 col-sm-12"  v-show="this.rendered.city"><span class="text-bold">City:</span>  {{this.rendered.city}}</div>
                        <div class="col-md-6 col-sm-12" v-show="this.rendered.affiliation"><span class="text-bold" >Affiliation:</span> {{this.rendered.affiliation}}</div>
                    </div>

                    <div class="row pt-3">
                        <div class="col-md-6 col-sm-12" v-show="this.rendered.disciplinary"><span class="text-bold" >Disciplinary:</span>  {{this.rendered.disciplinary}}</div>
                        <div class="col-md-6 col-sm-12" v-show="this.rendered.curriculumvitae"><span class="text-bold">CV:</span> <a :href="this.rendered.curriculumvitae" target="_blank">Download</a></div>
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
            }
        },
        mounted() {
            this.rendered = JSON.parse(this.item);
            console.log(this.rendered);
            console.log(this.role)
        },
        methods: {},
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
