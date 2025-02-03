<template>

    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{ this.rendered.name }} <span v-show="this.rendered.active">ACTIVE</span>
                    </h1>

                    <div class="card-action">
                        <a :href="route('email-templates.index')">
                            <i class="fa fa-arrow-circle-left fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>

                </div>

                <div class="card-body no-padding">
                    <div>
                        <h2>Subject: {{ this.rendered.subject }}</h2>
                    </div>

                    <div>
                        <h2>Body</h2>
                        <div v-html="this.rendered.body"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
export default {
    name: "EmailTemplateShow",
    props: ['item'],
    data: () => {
        return {
            rendered: {},
        }
    },
    mounted() {
        this.rendered = this.item;

        console.log(this.rendered);
    },
    methods: {},
}
</script>

<style scoped>

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
