<template>
    <div>
        <div class="modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <p>Are you sure you want to move this task?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary"
                                @click="onCancel">
                            Cancel
                        </button>
                        <button class="btn btn-primary"
                                @click="onConfirm">
                            Yes
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop show"></div>
    </div>
</template>

<script>
    export default {
        name: "ConfirmDialog.vue",
        methods: {
            onCancel() {
                this.$emit('dialogbox', 'cancel');
            },
            onConfirm() {
                this.$emit('dialogbox', 'confirm');
            }
        }
    }
</script>

<style scoped>
    .modal {
        display: block;
    }

</style>
