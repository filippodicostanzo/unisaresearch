<template>
    <div class="modal" v-if="visible" tabindex="-1" role="dialog"
        style="display: block; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ title }}</h5>
                    <button type="button" class="close" @click="cancel" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                            :style="`width: ${progress}%`" :aria-valuenow="progress" aria-valuemin="0"
                            aria-valuemax="100">
                            {{ progress }}%
                        </div>
                    </div>
                    <p class="mt-2 text-center">{{ statusMessage }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="cancel">Annulla</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "ModalPDF",
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        title: {
            type: String,
            default: "Generazione PDF in corso"
        },
        progress: {
            type: Number,
            default: 0
        },
        statusMessage: {
            type: String,
            default: "Preparazione dei documenti..."
        }
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        }
    }
}
</script>

<style scoped>
.progress {
    height: 25px;
}

.modal-content {
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}
</style>