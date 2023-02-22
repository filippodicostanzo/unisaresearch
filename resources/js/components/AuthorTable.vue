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
                            v-if="source==='admin'"></i>

                    </h1>


                    <div class="card-action" v-show="source==='author'">
                        <a :href="route('authors.create')">
                            <i class="fa fa-plus-circle fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body no-padding card-table">
                    <vue-good-table ref="my-table"
                                    @on-selected-rows-change="selectionChanged"
                                    :columns="columns"
                                    :rows="elements"
                                    :select-options="{ enabled: true, selectOnCheckboxOnly: true }"
                                    :search-options="{ enabled: true }"
                                    :pagination-options="{
                                        enabled: true,
                                        mode: 'records',
                                        perPage: 50,
                                        position: 'bottom',
                                        perPageDropdown: [50,100, 200],
                                        dropdownAllowAll: true,
                                        setCurrentPage: 1,
                                        jumpFirstOrLast : true,
                                        firstLabel : 'First Page',
                                        lastLabel : 'Last Page',
                                        nextLabel: 'next',
                                        prevLabel: 'prev',
                                        rowsPerPageLabel: 'Rows per page',
                                        ofLabel: 'of',
                                        pageLabel: 'page', // for 'pages' mode
                                        allLabel: 'All',
                                        infoFn: (params) => `my own page ${params.firstRecordOnPage}`,
                                        }"
                    >

                        <div slot="selected-row-actions" v-if="source==='admin'">
                            <button class="btn btn-success" @click="downloadSelected()">Download Selected</button>
                        </div>

                        <template slot="table-row" slot-scope="props">
                            <span v-if="props.column.field === 'options'">
                                <a class="btn btn-default btn-xs"
                                   :href="route('authors.show', {id: props.row.id}).withQuery({source: source})">
                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-default btn-xs"
                                       :href="route('authors.edit', {id: props.row.id})">
                                        <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-danger btn-xs" v-on:click="deleteItem(props.row.id, $event)">
                                        <i class="fas fa-minus-circle fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                            </span>
                        </template>
                    </vue-good-table>
                </div>

            </div>
        </div>
        <modal v-show="isModalVisible" :data="modalHTML" @close="closeModal"/>
    </div>
</template>

<script>


import Modal from './Modal';
import {VueGoodTable} from 'vue-good-table';
import {format} from "date-fns";

export default {
    name: "AuthorTable",
    components: {
        VueGoodTable,
        'modal': Modal,
    },
    props: ['title', 'items', 'source', 'role'],
    data: () => {
        return {
            elements: [],
            columns: [
                {
                    label: 'Id',
                    field: 'id',
                },
                {
                    label: 'First Name',
                    field: 'firstname',
                },
                {
                    label: 'Last Name',
                    field: 'lastname',
                },

                {
                    label: 'Email',
                    field: 'email',
                },


                {
                    label: 'Options',
                    field: 'options'
                },

            ],
            rendered: {},

            isModalVisible: false,
            modalHTML: {
                title: "Co Authors Guide",
                body: `<div>
                        <p>In this section you can manage the co-authors of the papers.</p>
                        <p>If you click on the button  <i class="fa fa-info-circle fa-fw"></i> you can add a new co-author.</p>
                        <p>In the table in the center of the page you can, for each item, carry out operations.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-eye fa-1x fa-lg"></i> allows you to see the details of this item.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-pencil-alt fa-1x fa-lg"></i> allows you to modify this item.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-minus-circle fa-1x fa-lg"></i> allows you to delete this item.</p>
                        </div>
                    `
            }
        }
    },
    mounted() {
        this.rendered = JSON.parse(this.items);

        this.rendered.forEach((item) => {


            let elem = {
                id: '',
                firstname: '',
                lastname: '',
                email: '',
                role: ''
            };

            elem.id = item.id;
            elem.firstname = item.firstname;
            elem.lastname = item.lastname;
            elem.email = item.email;

            this.elements.push(elem);
        });


    },
    methods: {
        selectionChanged() {
        },

        downloadSelected() {
            axios.post( this.url = window.location.href + '/generate',
                {authors: this.$refs['my-table'].selectedRows}, {responseType: 'blob'}
            ).then(
                (response) => {
                    const data = format(new Date(), 'yyyyMMddHHmmss');
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'coauthors-'+data+'.csv'); //or any other extension
                    document.body.appendChild(link);
                    link.click();
                });
        },
        deleteItem(id, e) {
            e.preventDefault();

            let alt = confirm('Are you sure to delete this item?');
            if (alt) {
                this.$http
                    .post("/admin/authors/" + id, {_method: 'delete'})
                    .then(response => {


                        if (response.status === 200) {

                            if (window.location.href.indexOf("all") > -1) {
                                window.location.href = '/admin/authors/all'
                            } else {
                                window.location.href = '/admin/authors'
                            }
                        }
                    })
                    .catch(error => {
                        alert(error.status)
                    });

            }
        },
        showModal() {
            this.isModalVisible = true;
        },
        closeModal() {
            this.isModalVisible = false;
        }

    },
}
</script>

<style scoped>

</style>
