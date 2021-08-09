<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">

            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{this.title}}
                        <i
                            class="fa fa-info-circle fa-fw pointer"
                            aria-hidden="true"
                            @click="showModal"
                        ></i>
                    </h1>
                    <div class="card-action">
                        <a :href="route('events.create')">
                            <i class="fa fa-plus-circle fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div class="card-action">
                        <span>Filter By Rooms: </span>
                        <select name="rooms" v-model="room" @change="onChange()" class="form-group">
                            <option value="">All</option>
                            <option v-for="room in this.rendered_rooms" :key="room.id" :value="room.id">
                                {{room.name}}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="card-body no-padding">
                    <div class="table-responsive">
                        <table class="table card-table table-striped">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Room</th>
                                <th>Active</th>
                                <th>Start</th>
                                <th>End</th>
                                <th class="text-right">Options</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr v-for="(item,k) in renderedPaginate" :key="k">

                                <td>{{item.id}}</td>
                                <td>{{item.title}}</td>
                                <td class="text-capitalize">{{item.type}}</td>
                                <td>{{item.room_fk.name}}</td>
                                <td> <span v-if="item.active==1"><i class="fa fas fa-check"></i> </span> <span v-else><i class="fa fas fa-close"></i></span></td>
                                <td>{{ format(new Date(item.start), 'dd/MM/yyyy HH:mm') }}</td>
                                <td>{{ format(new Date(item.end), 'dd/MM/yyyy HH:mm') }}</td>
                                <td class="text-right">
                                    <a class="btn btn-default btn-xs" :href="route('events.show', {id: item.id})">
                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-default btn-xs"
                                       :href="route('events.edit', {id: item.id})">
                                        <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-danger btn-xs" v-on:click="deleteItem(item.id, $event)">
                                        <i class="fas fa-minus-circle fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                </td>

                            </tr>

                            </tbody>

                        </table>

                        <vue-pagination v-model="page" :per-page="perpage" :records="pages"
                                        @input="callbackPagination(page)"></vue-pagination>

                    </div>
                </div>
            </div>
        </div>
        <modal v-show="isModalVisible" :data="modalHTML" @close="closeModal" />
    </div>
</template>

<script>

    import Pagination from 'vue-pagination-2';
    import {format} from 'date-fns';
    import Modal from './Modal'

    export default {
        name: "Event",
        components: {
            'vue-pagination': Pagination,
            'modal': Modal
        },
        props: ['title', 'items','rooms'],
        data: () => {
            return {
                currentPath:'',
                rendered: {},
                pages: 0,
                perpage: 20,
                page: 1,
                renderedPaginate: [],
                isModalVisible: false,
                rendered_rooms:[],
                room:'',
                format,
                modalHTML: {
                    title: "Event Guide",
                    body: `<div>
                        <p>In questa sezione è possibile gestire gli eventi.</p>
                        <p>Cliccando sul pulsante  <i class="fa fa-info-circle fa-fw"></i> è possibile aggiungere un nuovo evento.</p>
                        <p>Nella tabella presente al centro della pagina è possibile, per ogni item, effetuare delle operazioni.</p>
                        <p>Il pulsante <i aria-hidden="true" class="fas fa-eye fa-1x fa-lg"></i> permette di vedere i dettagli di questo item.</p>
                        <p>Il pulsante <i aria-hidden="true" class="fas fa-pencil-alt fa-1x fa-lg"></i> permette di modificare questo item.</p>
                        <p>Il pulsante <i aria-hidden="true" class="fas fa-minus-circle fa-1x fa-lg"></i> permette di cancellare questo item.</p>
                        <p>Gli eventi non potranno avere sovrapposizioni di orario e di classe.</p>
                        </div>
                    `
                }
            }
        },
        mounted() {
            this.currentPath = window.location.pathname;
            this.rendered = JSON.parse(this.items);



            if (this.rooms){
                this.rendered_rooms = JSON.parse(this.rooms);
            }
            this.pages = this.rendered.length;
            this.backup_filter = this.rendered;
            this.paginateData(this.page - 1, this.perpage)

        },
        methods: {
            deleteItem(id, e) {
                e.preventDefault();
                let alt = confirm('Are you sure to delete this item?');
                if (alt) {
                    this.$http
                        .delete("/admin/events/" + id)
                        .then(response => {

                            if (response.status == 200) {
                                window.location.href = '/admin/events'
                            }
                        })
                        .catch(error => {
                            alert(error.message)
                        });

                }
            },

            callbackPagination(page) {

                let start = 0;
                let end = 0;

                if (page == 1) {
                    start = page - 1;
                    end = (page - 1) + this.perpage;
                } else {
                    start = (page - 1) * this.perpage;
                    end = start + this.perpage;
                }
                this.paginateData(start, end);
            },

            paginateData(start, end) {
                this.renderedPaginate = this.rendered.slice(start, end);
            },
            showModal() {
                this.isModalVisible = true;
            },
            closeModal() {
                this.isModalVisible = false;
            },

            onChange() {

                this.rendered = this.backup_filter;
                if (this.room != '') {

                    this.rendered = this.rendered.filter(item => item.room == this.room);
                }

                this.pages = this.rendered.length;
                this.paginateData(this.page - 1, this.perpage);
            },

        },
    }
</script>

<style scoped>
.box-color {
    width: 20px;
    height: 20px;
}
</style>
