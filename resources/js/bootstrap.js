import axios from 'axios';
import flatpickr from 'flatpickr';
import Alpine from 'alpinejs';

window.axios = axios;
window.flatpickr = flatpickr;
window.Alpine = Alpine;

// Do not start Alpine here. Start Alpine after Livewire scripts
// have been loaded so Livewire can register its Alpine plugin.

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
