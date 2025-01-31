<template>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="m-0">
                    {{
                        isReviewer ? 'Send email to reviewer ' + this.rendered.reviewer_data.name + ' ' + this.rendered.reviewer_data.surname :
                            'Send email to ' + this.rendered.user_fk.name + ' ' + this.rendered.user_fk.surname
                    }}
                </h3>
                <a :href="route('posts.admin')" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Indietro
                </a>
            </div>
            <div class="card-body">
                <div class="row my-2">
                    <div class="col-12">
                        <h6>Paper Title: {{ this.rendered.title }}</h6>
                    </div>
                </div>
                <form @submit.prevent="submitForm">

                    <!-- Recipient Section (solo per gli autori) -->
                    <div v-if="!isReviewer" class="form-group mb-3" :class="{ 'has-error': $v.form.recipient.$error }">
                        <label>Recipient*</label>
                        <select
                            v-model="$v.form.recipient.$model"
                            class="form-control"
                            :class="{ 'is-invalid': $v.form.recipient.$error }"
                            required
                        >
                            <option value="">Select recipient</option>
                            <option value="author">Author only</option>
                            <option value="author-coauthors">Author and Co-authors</option>
                            <option value="coauthors">Co-authors only</option>
                        </select>
                        <div class="error" v-if="!$v.form.recipient.required && $v.form.recipient.$dirty">
                            Recipient selection is required
                        </div>
                    </div>

                    <!-- Email Subject -->
                    <div class="form-group mb-3" :class="{ 'has-error': $v.form.subject.$error }">
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

                    <!-- Variables Section -->
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

                    <!-- Email Body -->
                    <div class="form-group mb-3" :class="{ 'has-error': $v.form.body.$error }">
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

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" @click="resetForm">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="loading">
                            <i class="fas fa-paper-plane"></i>
                            {{ loading ? 'Sending...' : 'Send Email' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { minLength, required } from 'vuelidate/lib/validators'

export default {
    name: 'EmailTemplateForm',
    props: ['item'],

    validations() {
        const baseValidations = {
            subject: {
                required,
                minLength: minLength(3)
            },
            body: {
                required,
                minLength: minLength(10)
            }
        };

        if (this.isReviewer) {
            return {
                form: baseValidations,
            };
        }

        return {
            form: {
                ...baseValidations,
                recipient: { required }
            }
        };
    },

    data() {
        return {
            rendered: {
                user_fk: {},
                template_fk: {},
                reviewer_data: {},
                authors: []
            },
            isReviewer: false,
            editor: null,
            form: {
                recipient: '',
                review_decision: '',
                subject: '',
                body: '',
            },
            errors: {},
            submitStatus: null,
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
                ]
            },
            availableVariables: []
        }
    },

    mounted() {
        this.rendered = JSON.parse(this.item);
        console.log(this.rendered);
        this.isReviewer = !!this.rendered.is_reviewer;
        this.setupAvailableVariables();
    },

    methods: {
        setupAvailableVariables() {
            const commonVariables = [
                {key: '{$title}', label: 'Paper Title'},
                {key: '{$template}', label: 'Template'},
                {key: '{$comments}', label: 'Comments'},
            ];

            const authorVariables = [
                {key: '{$name}', label: 'Name'},
                {key: '{$surname}', label: 'Surname'},
                {key: '{$email}', label: 'Email'},
                {key: '{$coauthors}', label: 'Co-Authors'},
            ];

            const reviewerVariables = [
                {key: '{$reviewer_name}', label: 'Reviewer Name'},
                {key: '{$reviewer_surname}', label: 'Reviewer Surname'},
            ];

            this.availableVariables = [
                ...(this.isReviewer ? reviewerVariables : authorVariables),
                ...commonVariables
            ];
        },

        onEditorReady(editor) {
            this.editor = editor;
        },

        insertVariable(variable) {
            if (this.editor) {
                this.editor.insertHtml(variable + ' ');
            }
        },

        async submitForm() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                this.errors = {};

                // Gestione errori di validazione frontend
                if (this.isReviewer) {
                    if (this.$v.form.review_decision.$error) {
                        this.errors.review_decision = ['Review decision is required'];
                    }
                } else {
                    if (this.$v.form.recipient.$error) {
                        this.errors.recipient = ['Recipient selection is required'];
                    }
                }

                if (this.$v.form.subject.$error) {
                    this.errors.subject = [];
                    if (!this.$v.form.subject.required) {
                        this.errors.subject.push('L\'oggetto è obbligatorio');
                    }
                    if (!this.$v.form.subject.minLength) {
                        this.errors.subject.push('L\'oggetto deve essere di almeno 3 caratteri');
                    }
                }

                if (this.$v.form.body.$error) {
                    this.errors.body = [];
                    if (!this.$v.form.body.required) {
                        this.errors.body.push('Il contenuto è obbligatorio');
                    }
                    if (!this.$v.form.body.minLength) {
                        this.errors.body.push('Il contenuto deve essere di almeno 10 caratteri');
                    }
                }

                return;
            }

            try {
                this.loading = true;
                const formData = {
                    ...this.form,
                    post_id: this.rendered.id,
                    post_title: this.rendered.title,
                    is_reviewer: this.isReviewer
                };

                if (this.isReviewer) {
                    formData.reviewer = {
                        name: this.rendered.reviewer_data.name,
                        surname: this.rendered.reviewer_data.surname,
                        email: this.rendered.reviewer_data.email
                    };
                } else {
                    formData.author = {
                        name: this.rendered.user_fk.name,
                        surname: this.rendered.user_fk.surname,
                        email: this.rendered.user_fk.email
                    };
                    formData.coauthors = this.rendered.authors.map(coauthor => ({
                        name: coauthor.firstname,
                        surname: coauthor.lastname,
                        email: coauthor.email
                    }));
                }

                await axios.post(`/admin/posts/${this.rendered.id}/email`, formData);
                window.location.href = route('posts.admin');

            } catch (error) {
                this.loading = false;
                this.submitStatus = 'ERROR';

                if (error.response && error.response.data.errors) {
                    this.errors = error.response.data.errors;
                } else {
                    console.error('Error:', error);
                }
            }
        },

        resetForm() {
            this.form = {
                recipient: '',
                review_decision: '',
                subject: '',
                body: '',
            };
            this.$v.$reset();
            this.errors = {};
        }
    }
}
</script>

<style scoped>
.gap-2 { gap: 0.5rem; }
.btn-group { gap: 0.5rem; }
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
