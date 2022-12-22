<template>
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="card">
                <div class="card-header">
                    <h1 class="m0 text-dark card-title text-xl">
                        {{ this.title }}
                    </h1>
                    <div class="card-action">
                        <a :href="route('users.create')">
                            <i class="fa fa-plus-circle fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body no-padding card-table">
                    <vue-good-table ref="my-table"
                                    @on-selected-rows-change="selectionChanged"
                                    :columns="columns"
                                    :rows="elements"
                                    :select-options="{ enabled: false, selectOnCheckboxOnly: true }"
                                    :search-options="{ enabled: true }"
                                    :pagination-options="{
                                        enabled: true,
                                        mode: 'records',
                                        perPage: 100,
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

                        <template slot="table-row" slot-scope="props">
                            <span v-if="props.column.field === 'options'">
                                <a class="btn btn-default btn-xs"
                                   :href="route('users.show', {id: props.row.id})">
                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>
                                    <a class="btn btn-default btn-xs"
                                       :href="route('users.edit', {id: props.row.id})">
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
    </div>
</template>

<script>

import {VueGoodTable} from 'vue-good-table';

export default {


    name: "UserTable.vue",
    props: ['title', 'items'],
    components: {VueGoodTable},
    data() {
        return {
            elements: [],
            roles: [
                {value: 'superadministrator', text: 'Superadministrator'},
                {value: 'administrator', text: 'Administrator'},
                {value: 'reviewer', text: 'Reviewer'},
                {value: 'author', text: 'Author'},
                {value: 'user', text: 'User'}
            ],
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
                    label: 'Role',
                    field: 'role',
                    filterOptions: {
                        styleClass: 'class1', // class to be added to the parent th element
                        enabled: false, // enable filter for this column
                        placeholder: 'Filter This Thing', // placeholder for filter input
                        filterValue: '', // initial populated value for this filter
                        filterDropdownItems: this.roles, // dropdown (with selected values) instead of text input
                        //filterFn: this.columnFilterFn, //custom filter function that
                        trigger: 'enter', //only trigger on enter not on keyup
                    },
                },

                {
                    label: 'Options',
                    field: 'options'
                },

            ]
        }
    },
    fetch() {

    },
    mounted: function () {

/*
        this.$set(this.columns[4], 'filterOptions', {
            enabled: true,
            filterDropdownItems: this.roles,
        });*/

        this.rendered = JSON.parse(this.items);

        console.log(this.rendered);

        this.rendered.forEach((item) => {


            let elem = {
                id: '',
                firstname: '',
                lastname: '',
                email: '',
                role: ''
            };


            elem.id = item.id;
            elem.firstname = item.name;
            elem.lastname = item.surname;
            elem.email = item.email;

            if (item.roles[0]==null) {
                elem.role = ''
            }
            else if (item.roles[0].name==='supervisor') {
                elem.role = 'reviewer'
            }
            else if (item.roles[0].name==='researcher') {
                elem.role = 'author'
            }

            else {elem.role = item.roles[0].name}


            this.elements.push(elem);

        })
    },

    methods: {
        selectionChanged() {

        },


        deleteItem(id, e) {
            e.preventDefault();
            let alt = confirm('Are you sure to delete this item?');
            if (alt) {
                this.$http
                    .delete("/admin/users/" + id)
                    .then(response => {

                        if (response.status === 200) {
                            window.location.href = '/admin/users'
                        }
                    })
                    .catch(error => {
                        alert(error.message)
                    });

            }
        },
    },
}
</script>

<style scoped>

</style>
