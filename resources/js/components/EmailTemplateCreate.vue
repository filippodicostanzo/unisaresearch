<template>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="m-0">{{ isEditing ? 'Edit Email Template' : 'New Email Template' }}</h3>
                <a :href="route('email-templates.index')" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Indietro
                </a>
            </div>
            <div class="card-body">
                <form @submit.prevent="submitForm">
                    <!-- Nome Template e Select Template -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group" :class="{ 'has-error': $v.form.name.$error }">
                                <label>Template Name*</label>
                                <input
                                    v-model="$v.form.name.$model"
                                    class="form-control"
                                    :class="{ 'is-invalid': $v.form.name.$error }"
                                    required
                                >
                                <div class="error" v-if="!$v.form.name.required && $v.form.name.$dirty">
                                    Il nome del template è obbligatorio
                                </div>
                                <div class="error" v-if="!$v.form.name.minLength && $v.form.name.$dirty">
                                    Il nome deve essere di almeno 3 caratteri
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group" :class="{ 'has-error': $v.form.template.$error }">
                                <label>Template*</label>
                                <select
                                    v-model="$v.form.template.$model"
                                    class="form-control"
                                    :class="{ 'is-invalid': $v.form.template.$error }"
                                    required
                                >
                                    <option value="">Select a template</option>
                                    <option value="accepted-paper">Accepted Paper</option>
                                    <option value="paper-submission">Paper Submission</option>
                                    <option value="rejected-paper">Rejected Paper</option>
                                    <option value="reviewer-assignment">Reviewer Assignment</option>
                                    <option value="review-complete">Review Complete</option>
                                </select>
                                <div class="error" v-if="!$v.form.template.required && $v.form.template.$dirty">
                                    La selezione del template è obbligatoria
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Oggetto Email (full width) -->
                    <div class="form-group" :class="{ 'has-error': $v.form.subject.$error }">
                        <label>Email Subject*</label>
                        <input
                            v-model="$v.form.subject.$model"
                            class="form-control"
                            :class="{ 'is-invalid': $v.form.subject.$error }"
                            required
                        >
                        <div class="error" v-if="!$v.form.subject.required && $v.form.subject.$dirty">
                            L'oggetto è obbligatorio
                        </div>
                        <div class="error" v-if="!$v.form.subject.minLength && $v.form.subject.$dirty">
                            L'oggetto deve essere di almeno 3 caratteri
                        </div>
                    </div>

                    <!-- Variabili Disponibili -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="d-block mb-2">Available Variables:</label>
                            <div class="btn-group flex-wrap">
                                <button
                                    v-for="variable in availableVariables"
                                    :key="variable.key"
                                    type="button"
                                    class="btn btn-outline-primary mb-2 me-2"
                                    @click="insertVariable(variable.key)"
                                >
                                    {{ variable.label }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Body con CKEditor -->
                    <div class="form-group" :class="{ 'has-error': $v.form.body.$error }">
                        <label>Email Body*</label>
                        <ckeditor
                            v-model="$v.form.body.$model"
                            :config="editorConfig"
                            @ready="onEditorReady"
                        ></ckeditor>
                        <div class="error" v-if="!$v.form.body.required && $v.form.body.$dirty">
                            Il contenuto è obbligatorio
                        </div>
                        <div class="error" v-if="!$v.form.body.minLength && $v.form.body.$dirty">
                            Il contenuto deve essere di almeno 10 caratteri
                        </div>
                    </div>

                    <!-- Active Switch -->
                    <div class="form-group mb-3">
                        <div class="form-check form-switch">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                v-model="form.active"
                                role="switch"
                            >
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="resetForm">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="loading">
                            <i class="fas fa-save"></i>
                            {{ loading ? 'Saved...' : 'Save Template' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>

import {minLength, required} from 'vuelidate/lib/validators'

export default {
    name: 'EmailTemplateForm',

    props: {
        item: {
            type: Object,
            default: null
        }
    },

    validations: {
        form: {
            name: {
                required,
                minLength: minLength(3)
            },
            subject: {
                required,
                minLength: minLength(3)
            },
            template: {
                required
            },
            body: {
                required,
                minLength: minLength(10)
            }
        }
    },

    data() {
        return {
            editor: null, // Aggiungi questa riga
            form: {
                name: '',
                subject: '',
                template: '',
                body: '',
                active: true
            },
            errors: {},
            loading: false,
            editorConfig: {
                height: 300,
                toolbar: [
                    ['Bold', 'Italic', 'Underline', 'Strike'],
                    ['NumberedList', 'BulletedList'],
                    ['Link', 'Unlink'],
                    ['Source'],
                    ['Image', 'Table'],
                    ['Styles', 'Format'],
                    ['TextColor', 'BGColor'],
                    ['Maximize']
                ],
                on: {
                    instanceReady: function (ev) {
                        this.editor = ev.editor;
                    }
                }
            },
            availableVariables: [
                {key: '{$name}', label: 'Name'},
                {key: '{$surname}', label: 'Surname'},
                {key: '{$email}', label: 'Email'},
                {key: '{$title}', label: 'Title of the Paper/Abstract'},
                {key: '{$template}', label: 'Template'},
                {key: '{$coauthors}', label: 'Co-Authors'},
                {key: '{$comments}', label: 'Comments'},

            ]
        }
    },


    computed: {
        isEditing() {
            return !!this.item  // invece di this.template
        }
    },


    mounted() {
        if (this.item) {  // invece di this.template
            this.form = {...this.item}
        }
    },

    methods: {

        onEditorReady(editor) {
            this.editor = editor;
        },

        insertVariable(variable) {
            if (this.editor) {
                this.editor.insertHtml(variable + ' ');
            }
        },

        async submitForm() {
            this.$v.$touch()
            if (this.$v.$invalid) {
                this.errors = {}

                // Gestione errori di validazione frontend
                if (this.$v.form.name.$error) {
                    this.errors.name = []
                    if (!this.$v.form.name.required) {
                        this.errors.name.push('Il nome del template è obbligatorio')
                    }
                    if (!this.$v.form.name.minLength) {
                        this.errors.name.push('Il nome deve essere di almeno 3 caratteri')
                    }
                }

                if (this.$v.form.subject.$error) {
                    this.errors.subject = []
                    if (!this.$v.form.subject.required) {
                        this.errors.subject.push('L\'oggetto è obbligatorio')
                    }
                    if (!this.$v.form.subject.minLength) {
                        this.errors.subject.push('L\'oggetto deve essere di almeno 3 caratteri')
                    }
                }

                if (this.$v.form.template.$error) {
                    this.errors.template = []
                    if (!this.$v.form.template.required) {
                        this.errors.template.push('Il template è obbligatorio')
                    }
                }

                if (this.$v.form.body.$error) {
                    this.errors.body = []
                    if (!this.$v.form.body.required) {
                        this.errors.body.push('Il contenuto è obbligatorio')
                    }
                    if (!this.$v.form.body.minLength) {
                        this.errors.body.push('Il contenuto deve essere di almeno 10 caratteri')
                    }
                }

                return
            }

            try {
                this.loading = true
                const url = this.isEditing
                    ? `/admin/email-templates/${this.item.id}`
                    : '/admin/email-templates'

                const method = this.isEditing ? 'put' : 'post'

                const response = await axios[method](url, this.form)

                if (response.data.success) {
                    // Redirect con parametri query per il messaggio
                    window.location.href = `${route('email-templates.index')}?message=${encodeURIComponent(response.data.message)}&alert-class=alert-success`;
                }

            } catch (error) {
                if (error.response) {
                    // Redirect con messaggio di errore
                    const errorMessage = error.response.data.message || 'Si è verificato un errore';
                    window.location.href = `${route('email-templates.index')}?message=${encodeURIComponent(errorMessage)}&alert-class=alert-danger`;
                }
            } finally {
                this.loading = false
            }

        },

        resetForm() {
            if (this.template) {
                this.form = {...this.item};
            } else {
                this.form = {
                    name: '',
                    subject: '',
                    template: '',
                    body: '',
                    active: true
                };
            }
            this.errors = {};
        }
    }


}
</script>

<style scoped>
.gap-2 {
    gap: 0.5rem;
}

.btn-group {
    gap: 0.5rem;
}

.btn-outline-primary {
    margin-right: 5px;
    margin-bottom: 5px;
}

.error {
    color: red;
    font-size: 0.8em;
    margin-top: 5px;
}

.has-error input,
.has-error select,
.has-error textarea {
    border-color: red;
}
</style>
