import axios from 'axios';
import flatpickr from 'flatpickr';

window.axios = axios;
window.flatpickr = flatpickr;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
