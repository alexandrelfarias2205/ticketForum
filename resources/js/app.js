import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Alpine.js component for file uploads via AJAX POST.
 * Usage: x-data="fileUpload('<url>')"
 *
 * @param {string} uploadUrl  The endpoint URL to POST files to.
 */
window.fileUpload = (uploadUrl) => ({
    uploadUrl,
    dragging: false,
    uploading: false,
    progress: 0,
    uploadError: null,

    handleDrop(event) {
        this.dragging = false;
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            this.uploadFiles(files);
        }
    },

    handleFiles(files) {
        if (files.length > 0) {
            this.uploadFiles(files);
        }
    },

    uploadFiles(files) {
        const formData = new FormData();
        Array.from(files).forEach((file) => formData.append('file', file));

        this.uploading = true;
        this.progress = 0;
        this.uploadError = null;

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                this.progress = Math.round((e.loaded / e.total) * 100);
            }
        });

        xhr.addEventListener('load', () => {
            this.uploading = false;
            if (xhr.status >= 200 && xhr.status < 300) {
                this.progress = 0;
                // Reload Livewire component to show new attachment
                window.Livewire.dispatch('attachment-uploaded');
                window.location.reload();
            } else {
                try {
                    const data = JSON.parse(xhr.responseText);
                    this.uploadError = data.message ?? 'Erro ao enviar o arquivo. Tente novamente.';
                } catch {
                    this.uploadError = 'Erro ao enviar o arquivo. Tente novamente.';
                }
            }
        });

        xhr.addEventListener('error', () => {
            this.uploading = false;
            this.uploadError = 'Erro de conexão ao enviar o arquivo.';
        });

        xhr.open('POST', this.uploadUrl);
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.send(formData);
    },
});

Alpine.start();
