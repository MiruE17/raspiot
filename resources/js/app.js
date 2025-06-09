import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Koneksi Alpine.js dengan Livewire
document.addEventListener('alpine:init', () => {
    Alpine.data('modalData', () => ({
        open: false,
        toggleModal() {
            this.open = !this.open;
        },
        closeModal() {
            this.open = false;
        }
    }));
});
