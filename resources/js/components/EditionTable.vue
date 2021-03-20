<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{ this.title }}

                        <i
                            class="fa fa-info-circle fa-fw pointer"
                            aria-hidden="true"
                            @click="showModal"
                        ></i>
                    </h1>

                    <div class="card-action">
                        <a :href="route('editions.create')">
                            <i
                                class="fa fa-plus-circle fa-3x fa-fw"
                                aria-hidden="true"
                            ></i>
                        </a>
                    </div>
                </div>
                <div class="card-body no-padding">
                    <div class="table-responsive">
                        <table class="table card-table table-striped">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Active</th>
                                    <th class="text-right">Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(item, k) in renderedPaginate"
                                    :key="k"
                                >
                                    <td>{{ item.id }}</td>
                                    <td>{{ item.name }}</td>
                                    <td>
                                        <span v-if="item.active == 1"
                                            ><i class="fa fas fa-check"></i>
                                        </span>
                                        <span v-else
                                            ><i class="fa fas fa-close"></i
                                        ></span>
                                    </td>
                                    <td class="text-right">
                                        <a class="btn btn-default btn-xs" :href=" route('editions.show', { id: item.id })">
                                            <i class="fas fa-eye fa-1x fa-lg"
                                                aria-hidden="true"
                                            ></i>
                                        </a>
                                        <a class="btn btn-default btn-xs"
                                            :href=" route('editions.edit', { id: item.id })">
                                            <i
                                                class="fas fa-pencil-alt fa-1x fa-lg"
                                                aria-hidden="true"
                                            ></i>
                                        </a>
                                        <a class="btn btn-danger btn-xs" v-on:click="deleteItem(item.id, $event)">
                                            <i
                                                class="fas fa-minus-circle fa-1x fa-lg"
                                                aria-hidden="true"
                                            ></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <vue-pagination
                            v-model="page"
                            :per-page="perpage"
                            :records="pages"
                            @input="callbackPagination(page)"
                        ></vue-pagination>
                    </div>
                </div>
            </div>
        </div>

        <modal v-show="isModalVisible" :data="modalHTML" @close="closeModal" />
    </div>
</template>

<script>
import Pagination from "vue-pagination-2";
import Modal from "./Modal";

export default {
    name: "EditionTable",
    components: {
        "vue-pagination": Pagination,
        modal: Modal
    },
    props: ["title", "items"],
    data: () => {
        return {
            currentPath: "",
            rendered: {},
            pages: 0,
            perpage: 20,
            page: 1,
            renderedPaginate: [],
            isModalVisible: false,
            modalHTML: {
                title: "Edition Guide",
                body: `<div>
                        <p>In questa sezione è possibile gestire le varie edizioni del forum.</p>
                        <p>Cliccando sul pulsante  <i class="fa fa-info-circle fa-fw"></i> è possibile aggiungere una nuova edizione.</p>
                        <p>Nella tabella presente al centro della pagina, è possibile per ogni item, effetuare delle operazioni.</p>
                        <p>Il pulsante <i aria-hidden="true" class="fas fa-eye fa-1x fa-lg"></i> permette di vedere i dettagli di questo item</p>
                        <p>Il pulsante <i aria-hidden="true" class="fas fa-pencil-alt fa-1x fa-lg"></i> permette di modificare questo item.</p>
                        <p>Il pulsante <i aria-hidden="true" class="fas fa-minus-circle fa-1x fa-lg"></i> permette di cancellare questo item</p>
                        <p>È importante specificare che ci puo essere un'unica edizione attiva. Nel momento che un'edizione viene resa attiva tutte le altre vengono disattivate</p>
                        </div>
                    `
            }
        };
    },
    mounted() {
        this.currentPath = window.location.pathname;
        this.rendered = JSON.parse(this.items);
        this.pages = this.rendered.length;
        this.paginateData(this.page - 1, this.perpage);
    },
    methods: {
        deleteItem(id, e) {
            e.preventDefault();
            let alt = confirm("Are you sure to delete this item?");
            if (alt) {
                this.$http
                    .post("/admin/editions/" + id, { _method: "delete" })
                    .then(response => {
                        if (response.status === 200) {
                            window.location.href = "/admin/editions";
                        }
                    })
                    .catch(error => {
                        alert(error.status);
                    });
            }
        },

        callbackPagination(page) {
            let start = 0;
            let end = 0;

            if (page == 1) {
                start = page - 1;
                end = page - 1 + this.perpage;
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
        }
    }
};
</script>

<style scoped></style>
