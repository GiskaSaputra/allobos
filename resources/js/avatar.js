// Video Bos Allo (diam atau bicara). Saat gerak dikurangi, video tidak diputar dan poster diam yang tampil.
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export function playClip(video, src) {
    if (!video || reduce) return;
    if (!video.src.endsWith(src)) video.src = src;
    video.play().catch(() => {});
}

// Video hiasan baru diunduh dan diputar saat terlihat, lalu dijeda saat keluar layar.
export function initAvatars(root = document) {
    const videos = root.querySelectorAll('video[data-clip]');
    if (!videos.length || reduce) return;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(({ target, isIntersecting }) => {
            if (isIntersecting) playClip(target, target.dataset.clip);
            else target.pause();
        });
    }, { rootMargin: '200px 0px' });
    videos.forEach((video) => observer.observe(video));
}
