// Pratinjau sertifikat: iframe selebar lembar A4 (1123 px) diskalakan supaya pas dengan lebar wadahnya.
export function initScaleFrames() {
    document.querySelectorAll('[data-scale-frame]').forEach((box) => {
        const frame = box.querySelector('iframe');
        const fit = () => { frame.style.transform = `scale(${box.clientWidth / 1123})`; };
        new ResizeObserver(fit).observe(box);
        fit();
    });
}
