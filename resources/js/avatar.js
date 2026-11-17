// Video Bos Allo (diam atau bicara). Saat gerak dikurangi, video tidak diputar dan poster diam yang tampil.
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export function playClip(video, src) {
    if (!video || reduce) return;
    if (!video.src.endsWith(src)) video.src = src;
    video.play().catch(() => {});
}

export function initAvatars(root = document) {
    root.querySelectorAll('video[data-clip]').forEach((video) => playClip(video, video.dataset.clip));
}
