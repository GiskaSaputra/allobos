// Gerak beranda. Tidak berjalan bila pengguna meminta kurangi gerak.
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!reduce) {
    document.documentElement.classList.add('js-motion');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-in');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -10% 0px' });

    document.querySelectorAll('[data-reveal]').forEach((el) => observer.observe(el));
}
