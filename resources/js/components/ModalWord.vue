<template>
    <div class="modal fade" id="wordExportModal" tabindex="-1" role="dialog" aria-labelledby="wordExportModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="wordExportModalLabel">Esportazione Word in corso</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" :disabled="isGenerating">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div v-if="isGenerating">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="sr-only">Generazione in corso...</span>
                        </div>
                        <p>{{ status }}</p>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar"
                                 :style="{ width: progress + '%' }"
                                 :aria-valuenow="progress"
                                 aria-valuemin="0"
                                 aria-valuemax="100">{{ progress }}%</div>
                        </div>
                    </div>
                    <div v-else-if="isComplete">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle fa-2x mr-2"></i> Esportazione completata con successo!
                        </div>
                        <p>Il download del file dovrebbe iniziare automaticamente.</p>
                        <p>Se il download non inizia, <a href="#" @click.prevent="triggerDownload" class="btn btn-link">clicca qui</a>.</p>
                    </div>
                    <div v-else-if="hasError">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle fa-2x mr-2"></i> Errore durante l'esportazione
                        </div>
                        <p>{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" :disabled="isGenerating">Chiudi</button>
                    <button v-if="isComplete" type="button" class="btn btn-primary" @click="triggerDownload">
                        <i class="fas fa-download"></i> Download
                    </button>
                    <button v-if="hasError" type="button" class="btn btn-primary" @click="retry">
                        <i class="fas fa-redo"></i> Riprova
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ModalWord',

    props: {
        selectedRows: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            isGenerating: false,
            progress: 0,
            status: "Inizializzazione...",
            isComplete: false,
            hasError: false,
            errorMessage: "",
            downloadUrl: null,
            fileName: "",
            progressInterval: null
        };
    },

    methods: {
        show() {
            this.resetState();
            $('#wordExportModal').modal('show');
            this.startGeneration();
        },

        resetState() {
            // Ferma il timer se è in esecuzione
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }

            this.isGenerating = false;
            this.progress = 0;
            this.status = "Inizializzazione...";
            this.isComplete = false;
            this.hasError = false;
            this.errorMessage = "";

            // Rilascia l'URL se esiste
            if (this.downloadUrl) {
                URL.revokeObjectURL(this.downloadUrl);
                this.downloadUrl = null;
            }
            this.fileName = "";
        },

        startGeneration() {
            if (!this.selectedRows || this.selectedRows.length === 0) {
                this.hasError = true;
                this.errorMessage = "Seleziona almeno un elemento prima di esportare.";
                return;
            }

            // Imposta lo stato di generazione
            this.isGenerating = true;
            this.progress = 10;
            this.status = "Preparazione dei dati...";

            // Simula progressione
            this.progressInterval = setInterval(() => {
                if (this.progress < 90) {
                    this.progress += 5;

                    if (this.progress > 30 && this.progress < 60) {
                        this.status = "Generazione documento in corso...";
                    } else if (this.progress >= 60 && this.progress < 80) {
                        this.status = "Applicazione stili e formattazione...";
                    } else if (this.progress >= 80) {
                        this.status = "Finalizzazione documento...";
                    }
                }
            }, 300);

            // Esegui la richiesta Ajax per generare il file Word
            axios.post(
                window.location.href + '/generate-word',
                {papers: this.selectedRows},
                {responseType: 'blob'}
            ).then(
                (response) => {
                    // Ferma la simulazione di progressione
                    this.stopProgressSimulation();

                    // Imposta lo stato di completamento
                    this.progress = 100;
                    this.status = "Documento generato con successo!";
                    this.isGenerating = false;
                    this.isComplete = true;

                    // Prepara il download
                    const data = this.formatDate(new Date());
                    this.fileName = 'papers-'+data+'.docx';
                    const blob = new Blob([response.data], {
                        type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    });
                    this.downloadUrl = URL.createObjectURL(blob);

                    // Avvia il download automaticamente
                    this.triggerDownload();
                }
            ).catch(error => {
                // Ferma la simulazione di progressione
                this.stopProgressSimulation();

                // Imposta lo stato di errore
                this.isGenerating = false;
                this.hasError = true;
                this.progress = 0;
                this.errorMessage = 'Si è verificato un errore durante la generazione del documento.';

                console.error('Error generating Word document:', error);
            });
        },

        stopProgressSimulation() {
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }
        },

        formatDate(date) {
            const pad = (num) => String(num).padStart(2, '0');

            const year = date.getFullYear();
            const month = pad(date.getMonth() + 1);
            const day = pad(date.getDate());
            const hours = pad(date.getHours());
            const minutes = pad(date.getMinutes());
            const seconds = pad(date.getSeconds());

            return `${year}${month}${day}${hours}${minutes}${seconds}`;
        },

        triggerDownload() {
            if (this.downloadUrl && this.fileName) {
                const link = document.createElement('a');
                link.href = this.downloadUrl;
                link.setAttribute('download', this.fileName);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        },

        retry() {
            this.resetState();
            this.startGeneration();
        }
    },

    beforeDestroy() {
        // Pulizia per evitare memory leak
        this.stopProgressSimulation();

        if (this.downloadUrl) {
            URL.revokeObjectURL(this.downloadUrl);
        }
    }
};
</script>
