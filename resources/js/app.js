import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { initTheme } from './theme';
import { initForms } from './forms';
import { initCharts } from './charts';
import { initSignaturePads } from './signature-pad';
import { walletSign } from './wallet';
import { initAvatars } from './avatar';

window.Alpine = Alpine;

initTheme();
initAvatars();
initForms();
initCharts();
initSignaturePads();
Alpine.data('walletSign', walletSign);

const sentinel = document.getElementById('nav-sentinel');
const nav = document.querySelector('[data-nav]');
if (sentinel && nav) {
    new IntersectionObserver(([entry]) => nav.toggleAttribute('data-scrolled', !entry.isIntersecting)).observe(sentinel);
}

Livewire.start();
