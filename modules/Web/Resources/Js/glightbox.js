import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.min.css';

let lightbox;

function initGLightbox() {
    if (lightbox) {
        lightbox.destroy();
    }
    lightbox = GLightbox({
        selector: '.glightbox',
        openEffect: 'none',
        closeEffect: 'none',
        touchNavigation: true,
        loop: true,
        autoplayVideos: false,
        zoomable: false,
    });
}

// Beim ersten Laden
document.addEventListener('DOMContentLoaded', function() {
    initGLightbox();
});

// Nach Livewire Updates
document.addEventListener('livewire:navigated', function() {
    initGLightbox();
});

// Nach Livewire Component Updates
document.addEventListener('livewire:update', function() {
    initGLightbox();
});

// Custom Event von Livewire Component
document.addEventListener('livewire:init', () => {
    Livewire.on('reinit-glightbox-delayed', () => {
        // 300ms Delay
        setTimeout(() => {
            initGLightbox();
        }, 300);
    });
});
