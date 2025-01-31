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
                            @click="showModal">
                        </i>
                    </h1>
                    <div class="card-action">
                        <a :href="route('email-templates.create')">
                            <i class="fa fa-plus-circle fa-3x fa-fw" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body no-padding card-table">
                    <vue-good-table
                        ref="my-table"
                        @on-selected-rows-change="selectionChanged"
                        :columns="columns"
                        :rows="elements"
                        :select-options="{
                            enabled: true,
                            selectOnCheckboxOnly: true
                        }"
                        :search-options="{ enabled: true }"
                        :pagination-options="{
                            enabled: true,
                            mode: 'records',
                            perPage: 50,
                            position: 'bottom',
                            perPageDropdown: [50, 100, 200],
                            dropdownAllowAll: true,
                            setCurrentPage: 1,
                            jumpFirstOrLast: true,
                            firstLabel: 'First Page',
                            lastLabel: 'Last Page',
                            nextLabel: 'next',
                            prevLabel: 'prev',
                            rowsPerPageLabel: 'Rows per page',
                            ofLabel: 'of',
                            pageLabel: 'page',
                            allLabel: 'All',
                        }"
                    >
                        <template slot="table-row" slot-scope="props">
                            <span v-if="props.column.field === 'active'">
                                <i :class="props.row.active ? 'fas fa-check text-success' : 'fas fa-times text-danger'"></i>
                            </span>
                            <span v-else-if="props.column.field === 'options'">
                                <a class="btn btn-default btn-xs"
                                   :href="route('email-templates.show', props.row.id)">
                                    <i class="fas fa-eye fa-1x fa-lg" aria-hidden="true"></i>
                                </a>
                                <a class="btn btn-default btn-xs"
                                   :href="route('email-templates.edit', props.row.id)">
                                    <i class="fas fa-pencil-alt fa-1x fa-lg" aria-hidden="true"></i>
                                </a>
                                <a class="btn btn-danger btn-xs" @click="deleteItem(props.row.id, $event)">
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
import { VueGoodTable } from 'vue-good-table';

export default {
    name: "EmailTemplateTable",
    components: {
        VueGoodTable,
        'modal': Modal,
    },
    props: ['title', 'items'],

    data: () => {
        return {
            elements: [],
            columns: [
                {
                    label: 'Id',
                    field: 'id',
                },
                {
                    label: 'Nome',
                    field: 'name',
                },
                {
                    label: 'Oggetto',
                    field: 'subject',
                },
                {
                    label: 'Template',
                    field: 'template',
                },
                {
                    label: 'Attivo',
                    field: 'active',
                },

                {
                    label: 'Opzioni',
                    field: 'options'
                },
            ],
            isModalVisible: false,
            modalHTML: {
                title: "Email Templates Guide",
                body: `<div>
                    <p>In questa sezione puoi gestire i template delle email.</p>
                    <p>Per creare un nuovo template, clicca sul pulsante <i class="fa fa-plus-circle"></i></p>
                    <p>Per ogni template puoi:</p>
                    <p>- Visualizzare i dettagli con <i class="fas fa-eye"></i></p>
                    <p>- Modificare il template con <i class="fas fa-pencil-alt"></i></p>
                    <p>- Eliminare il template con <i class="fas fa-minus-circle"></i></p>
                </div>`
            }
        }
    },

    mounted() {
        try {
            // Verifica se items è una stringa e non è vuota
            if (this.items && typeof this.items === 'string') {
                this.rendered = JSON.parse(this.items);
            } else if (Array.isArray(this.items)) {
                // Se items è già un array, usalo direttamente
                this.rendered = this.items;
            } else {
                // Se items è vuoto o non valido, inizializza un array vuoto
                this.rendered = [];
            }

            // Procedi con la popolazione degli elementi
            this.rendered.forEach((item) => {
                let elem = {
                    id: item.id,
                    name: item.name,
                    subject: item.subject,
                    active: item.active,
                    template: item.template,
                };
                this.elements.push(elem);
            });
        } catch (error) {
            console.error('Errore nel parsing dei dati:', error);
            this.elements = []; // Inizializza un array vuoto in caso di errore
        }
    },

    methods: {
        selectionChanged(params) {
            console.log('Selection changed:', params);
        },

        deleteItem(id, e) {
            e.preventDefault();

            if (confirm('Are you sure you want to delete this template?')) {
                this.$http
                    .delete(`/admin/email-templates/${id}`)
                    .then(response => {
                        if (response.data.success) {
                            // Utilizziamo lo stesso approccio di redirect con parametri query
                            // che viene usato nel form di creazione e modifica
                            window.location.href = `${route('email-templates.index')}?message=${encodeURIComponent(response.data.message)}&alert-class=alert-success`;
                        }
                    })
                    .catch(error => {
                        // Gestiamo gli errori nello stesso modo degli altri metodi
                        const errorMessage = error.response?.data?.message || 'An error occurred';
                        window.location.href = `${route('email-templates.index')}?message=${encodeURIComponent(errorMessage)}&alert-class=alert-danger`;
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
.pointer {
    cursor: pointer;
}

.card-action {
    position: absolute;
    right: 15px;
    top: 15px;
}

.btn-xs {
    padding: 0.2rem 0.4rem;
    margin: 0 0.2rem;
}
</style>
