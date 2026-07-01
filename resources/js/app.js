import './bootstrap';

import Alpine from 'alpinejs';

// Swiper (carousel) untuk hero slider beranda — Requirement 35.
// Hanya modul yang dipakai yang diimpor agar bundle tetap ramping.
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, Keyboard, A11y } from 'swiper/modules';

// Ekspos ke window agar bisa diinisialisasi dari Blade (Alpine x-init).
window.Swiper = Swiper;
window.SwiperModules = { Navigation, Pagination, Autoplay, Keyboard, A11y };

window.Alpine = Alpine;

// Komponen carousel baris (dipakai daftar layanan per kategori).
Alpine.data('rowCarousel', () => ({
    swiper: null,
    init() {
        if (! window.Swiper) {
            return;
        }
        this.swiper = new window.Swiper(this.$refs.swiper, {
            modules: Object.values(window.SwiperModules ?? {}),
            slidesPerView: 1.15,
            spaceBetween: 20,
            grabCursor: true,
            navigation: {
                nextEl: this.$refs.next,
                prevEl: this.$refs.prev,
            },
            breakpoints: {
                640: { slidesPerView: 2.2, spaceBetween: 20 },
                1024: { slidesPerView: 3, spaceBetween: 24 },
                1280: { slidesPerView: 4, spaceBetween: 24 },
            },
        });
    },
}));

Alpine.start();
