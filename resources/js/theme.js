export function initTheme() {
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-theme-toggle]')) return;
        const dark = document.documentElement.classList.toggle('dark');
        try {
            localStorage.setItem('tema', dark ? 'gelap' : 'terang');
        } catch (e) {
            // Penyimpanan peramban diblokir: tema tetap berlaku untuk halaman ini saja.
        }
        document.dispatchEvent(new CustomEvent('tema', { detail: { dark } }));
    });
}
