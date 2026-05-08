import axios from 'axios';
import flatpickr from 'flatpickr';
import Alpine from 'alpinejs';

window.axios = axios;
window.flatpickr = flatpickr;
window.Alpine = Alpine;

Alpine.start();

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
