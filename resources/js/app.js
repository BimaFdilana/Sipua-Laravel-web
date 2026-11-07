import './bootstrap';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);

// Chart.js hanya dimuat di halaman yang membutuhkan grafik.
window.loadChart = () => import('chart.js/auto').then((m) => m.default);

window.Alpine = Alpine;

Alpine.start();
