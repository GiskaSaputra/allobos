// Gerak halaman publik: bagian dan kartu muncul bertahap saat digulir. Tidak berjalan bila pengguna meminta kurangi gerak.
export function initMotion() {
    if (!document.body.hasAttribute('data-motion')) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    document.documentElement.classList.add('js-motion');

    // Kartu di dalam kisi diberi urutan supaya munculnya berurutan, tidak serentak.
    document.querySelectorAll('main .card').forEach((card) => {
        if (card.closest('[data-reveal]') || card.hasAttribute('data-reveal')) return;
        card.setAttribute('data-reveal', '');
        const siblings = [...card.parentElement.children];
        card.style.setProperty('--i', String(Math.min(siblings.indexOf(card), 6)));
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-in');
            observer.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -8% 0px' });

    document.querySelectorAll('[data-reveal]').forEach((el) => observer.observe(el));
}
