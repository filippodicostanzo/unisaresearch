<template>
    <div class="card">
        <div class="card-header">
            <h2>Welcome {{ rendered.name }} {{ rendered.surname }}  to the NFS manuscript management platform!</h2>
        </div>
        <div class="card-body">
                <h6>To add a new manuscript, please insert your co-authors first by clicking <a href="admin/authors">HERE</a> or on "My co-authors" on the left.</h6>
                <h6>Then, add the manuscript by clicking <a href="admin/posts">HERE</a> or on "My Papers" on the left.</h6>
        </div>
    </div>
</template>

<script>
export default {
    name: "Welcome.vue",
    props: ['user'],
    data() {
        return {
            rendered: {
                name: '',
                surname:''
            }
        }
    },
    mounted() {
        this.rendered = JSON.parse(this.user);

    }
}
</script>

<style scoped>

</style>
