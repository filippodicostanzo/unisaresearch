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
                            v-if="source==='admin' ||source==='reviewer'"></i>
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
                                    styleClass="vgt-table striped"
                                    :columns="columns"
                                    :rows="elements"
                                    :select-options="{ enabled: true, selectOnCheckboxOnly: true }"
                                    :search-options="{ enabled: true }"
                                    :pagination-options="{
                                        enabled: true,
                                        mode: 'records',
                                        perPage: 20,
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

                            <span v-if="props.column.field==='state'">
                                <span :style="`background-color:${props.row.state_fk.color}`"
                                      class="post-status">{{ props.row.state_fk.name }}</span>
                            </span>

                            <span v-else-if="props.column.field==='reviews'">


                                    <span v-for="reviews in props.row.users" v-if="source==='admin'">

                                        <i class="far fa-circle" v-if="!reviews.checked"
                                           data-toggle="tooltip" data-placement="top"
                                           :title="reviews.name+' '+ reviews.surname"></i>
                                        <i class="fas fa-circle" v-if="reviews.checked" data-toggle="tooltip"
                                           data-placement="top" :title="reviews.name+' '+ reviews.surname"></i></span>



                                    <span v-for="reviews in  props.row.users" v-if="source==='reviewer'">
                                        <span v-if="reviews.id === json_user.id">
                                        <i class="far fa-circle" v-if="!reviews.checked"
                                           data-toggle="tooltip" data-placement="top"
                                           :title="reviews.name+' '+ reviews.surname"></i>
                                        <i
                                            class="fas fa-circle" v-if="reviews.checked" data-toggle="tooltip"
                                            data-placement="top" :title="reviews.name+' '+ reviews.surname"></i></span>
                                    </span>

                            </span>

                            <span v-else-if="props.column.field === 'options'">

                                 <a class="btn btn-default btn-xs"
                                    :href="route('posts.show', props.row.id).withQuery({ source: source })">

                                        <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs" :href="'../reviews/create?id='+ props.row.id"
                                       v-if="source==='reviewer' && props.row.state ==3">
                                        <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.link', {id: props.row.id})"
                                       v-if="source==='admin' && props.row.state!=6">
                                        <i class="fas fa-link fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.valid', {id: props.row.id})"
                                       v-if="source==='admin' && props.row.state!=6">
                                        <i class="fas fa-clipboard-check fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>

                                    <a class="btn btn-default btn-xs"
                                       :href="route('posts.edit', {id: props.row.id})"
                                       v-if="source==='author' && props.row.state == 1">
                                        <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                                    </a>


                                    <a class="btn btn-danger btn-xs" v-on:click="deleteItem(props.row.id, $event)"
                                       v-if="source==='admin' || props.row.state ==1">
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

import {VueGoodTable} from 'vue-good-table';
import Modal from "./Modal";
import {format} from 'date-fns'

export default {
    name: "PostsVueTable.vue",
    components: {VueGoodTable, 'modal': Modal},
    props: ['id', 'title', 'items', 'role', 'reviews', 'user', 'statuses', 'source', 'categories'],

    data() {
        return {
            elements: [],
            json_categories: [],
            paper_state: [
                {value: 'Created', text: 'Created'},
                {value: 'Review', text: 'Review'},
                {value: 'Submitted', text: 'Submitted'},
                {value: 'Accepted', text: 'Accepted'},
                {value: 'Rejected', text: 'Rejected'},
                {value: 'Final', text: 'Final'}
            ],
            columns: [
                {
                    label: 'ID',
                    field: 'id',

                },
                {
                    label: 'Title',
                    field: 'title',

                },
                {
                    label: 'Authors',
                    field: this.colAuthors,
                    html: true,
                    hidden: this.hiddenReviewers(),
                },

                {
                    label: 'Topic',
                    field: 'topic'
                },

                {
                    label: 'Template',
                    field: 'template'
                },

                {
                    label: 'Date',
                    field: 'date',
                    type: 'date',
                    dateInputFormat: 'yyyy-MM-dd\'T\'HH:mm:ss.SSSSSS\'Z\'', //"2022-12-16 10:35:57"
                    dateOutputFormat: 'yyyy/MM/dd', // outputs Mar 16th 2018
                },

                {
                    label: 'State',
                    field: 'state_def',
                    //field: this.colStatus,
                    html: true,
                    tdClass: this.tdClassFunc,
                    filterOptions: {
                        styleClass: 'class1', // class to be added to the parent th element
                        enabled: true, // enable filter for this column
                        placeholder: 'Filter This Thing', // placeholder for filter input
                        filterValue: '', // initial populated value for this filter
                        filterDropdownItems: this.paper_state, // dropdown (with selected values) instead of text input
                        //filterFn: this.columnFilterFn, //custom filter function that
                        trigger: 'enter', //only trigger on enter not on keyup
                    },
                },
                {
                    label: 'Reviews',
                    field: 'reviews',
                    hidden: this.hiddenAuthors()
                },


                /*                {
                                    label: 'Role',
                                    field: 'role',
                                    filterOptions: {
                                        styleClass: 'class1', // class to be added to the parent th element
                                        enabled: true, // enable filter for this column
                                        placeholder: 'Filter This Thing', // placeholder for filter input
                                        filterValue: '', // initial populated value for this filter
                                        filterDropdownItems: this.roles, // dropdown (with selected values) instead of text input
                                        //filterFn: this.columnFilterFn, //custom filter function that
                                        trigger: 'enter', //only trigger on enter not on keyup
                                    },
                                },*/

                {
                    label: 'Options',
                    field: 'options'
                },

            ],
            isModalVisible: false,
            modalHTML: {
                title: "Papers Guide",
                body: `<div>
                        <p>In this section you can manage the papers.</p>
                        <p>In the table in the center of the page you can, for each item, carry out operations.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-eye fa-1x fa-lg"></i> you to see the details of this item.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-link fa-1x fa-lg"></i> allows administrators to assign this paper to reviewers.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-clipboard-check fa-1x fa-lg"></i> allows administrators to read the reviews and to accept or reject the paper.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-pencil-alt fa-1x fa-lg"></i> allows a reviewers to write a review.</p>
                        <p>The button <i aria-hidden="true" class="fas fa-minus-circle fa-1x fa-lg"></i> allows you to delete this item.</p>
                        </div>
                    `
            }
        }
    },

    mounted() {

        console.log(this.source);
        console.log(JSON.parse(this.categories));
        this.json_reviews = JSON.parse(this.reviews);
        this.json_categories = JSON.parse(this.categories);


        this.$set(this.columns[3], 'filterOptions', {
            enabled: true,
            filterDropdownItems: this.json_categories,
        })

        this.$set(this.columns[6], 'filterOptions', {
            enabled: true,
            filterDropdownItems: this.paper_state,
        });

        this.rendered = JSON.parse(this.items);

        this.checkedReviews(this.rendered, this.json_reviews)

        console.log(this.rendered);

        this.rendered.forEach((item) => {


            let elem = {
                id: '',
                title: '',
                authors: '',
                email: '',
                role: '',
                user_fk: '',
                topic: '',
                template: '',
                date: '',
                state: '',
                users: [],
                state_def: '',
            };

            elem.title = item.title;
            elem.id = item.id;
            elem.authors = item.authors;
            elem.user_fk = item.user_fk;
            elem.topic = item.category_fk.name;
            elem.template = item.template_fk.name;
            elem.date = item.created_at;
            elem.state = item.state;
            elem.users = item.users;
            elem.state_fk = item.state_fk;
            elem.state_def = item.state_fk.name;


            this.elements.push(elem);

        })
    },
    methods: {

        deleteItem(id, e) {
            e.preventDefault();
            let alt = confirm('Are you sure to delete this item?');
            if (alt) {
                this.$http
                    //.delete("/admin/posts/" + id)

                    .post('/admin/posts/' + id, {_method: 'delete'})

                    .then(response => {
                        if (response.status === 200) {
                            window.location.href = '/admin/posts/all'
                        }
                    })
                    .catch(error => {
                        alert(error.message)
                    });

            }
        },

        checkedReviews(items, reviews) {
            items.map((el) => {
                el.users.map((us) => {
                    reviews.map((rew) => {
                        if (us.pivot.user_id == rew.supervisor && us.pivot.post_id == rew.post) {
                            if (rew.result !== 'review') {
                                us.checked = true;
                            }
                        }
                    })
                })
            });

        },

        selectionChanged() {

        },

        hiddenReviewers() {
            return this.source === 'reviewer';
        },

        hiddenAuthors() {
            return this.source === 'author';
        },

        downloadSelected() {
            axios.post( this.url = window.location.href + '/generate',
                {papers: this.$refs['my-table'].selectedRows}, {responseType: 'blob'}
            ).then(
                (response) => {
                    const data = format(new Date(), 'yyyyMMddHHmmss');
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'orders-'+data+'.csv'); //or any other extension
                    document.body.appendChild(link);
                    link.click();


                });
        },

        onCancel() {
            console.log('User cancelled the loader.')
        },

        colAuthors(rowObj) {
            let coauthors = '';
            if (rowObj.authors.length > 0) {
                rowObj.authors.forEach((author, index) => {
                    if (index < rowObj.authors.length) {
                        coauthors += ' - ';
                    }
                    coauthors += `${author.firstname} ${author.lastname}`;
                })
            }
            return `${rowObj.user_fk.name} ${rowObj.user_fk.surname} ${coauthors}`;

        },

        colStatus(rowObj) {
            return `<span>${rowObj.state_fk.name}</span>`
        },

        tdClassFunc(row) {

            switch (row.state) {
                case '1' : {
                    return 'celeste-class post-status'
                }
                case '2' : {
                    return 'orange-class post-status'
                }
                case '3' : {
                    return 'blue-class post-status'
                }
                case '4' : {
                    return 'green-class post-status'
                }
                case '5' : {
                    return 'red-class post-status';
                }
                default : {
                    return 'violet-class post-status';
                }
            }

        },

        showModal() {
            this.isModalVisible = true;
        },
        closeModal() {
            this.isModalVisible = false;
        }

    }
}
</script>

<style lang="scss">

.post-status {
    span {
        padding: 5px 10px;
        border-radius: 20px;
    }
}

.celeste-class {
    span {
        background: rgb(95, 241, 205);
        color: black;
    }
}

.orange-class {
    span {
        background: rgb(228, 161, 17);
        color: black;
    }
}

.blue-class {
    span {
        background-color: rgb(1, 96, 198);
        color: white;
    }
}

.green-class {
    span {
        background-color: rgb(14, 198, 1);
        color: black;
    }
}

.red-class {
    span {
        background-color: rgb(228, 1, 1);
        color: white;
    }
}

.violet-class {
    span {
        background-color: rgb(185, 11, 249);
        color: white;
    }
}

.card-table {
    .vgt-selection-info-row {
        .btn {
            width: 100%;
        }
    }
}

</style>
