import axios from 'axios';
import flatpickr from 'flatpickr';
import Alpine from 'alpinejs';

window.axios = axios;
window.flatpickr = flatpickr;
window.Alpine = Alpine;

// Don't start Alpine here - Livewire handles Alpine initialization via @livewireScripts
// Alpine.start();

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
