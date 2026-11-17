// Kanvas tanda tangan dengan Pointer Events. Hasil disimpan sebagai PNG transparan ke input tersembunyi.
export function initSignaturePads() {
    document.querySelectorAll('[data-signature-pad]').forEach((root) => {
        const canvas = root.querySelector('canvas');
        const output = root.querySelector('[data-signature-output]');
        const form = root.closest('form');
        const save = form?.querySelector('[data-signature-save]');
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let dirty = false;
        let last = null;

        const resize = () => {
            const ratio = Math.min(window.devicePixelRatio || 1, 2);
            const { width, height } = canvas.getBoundingClientRect();
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            ctx.scale(ratio, ratio);
            ctx.lineWidth = 2.4;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = 'rgb(20 26 12)';
            dirty = false;
            update();
        };

        const point = (e) => {
            const rect = canvas.getBoundingClientRect();
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        };

        const update = () => {
            output.value = dirty ? canvas.toDataURL('image/png') : '';
            if (save) save.disabled = !dirty && !form.querySelector('input[type=file]')?.files.length;
        };

        canvas.addEventListener('pointerdown', (e) => {
            drawing = true;
            last = point(e);
            canvas.setPointerCapture(e.pointerId);
        });
        canvas.addEventListener('pointermove', (e) => {
            if (!drawing) return;
            const p = point(e);
            ctx.beginPath();
            ctx.moveTo(last.x, last.y);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            last = p;
            dirty = true;
        });
        ['pointerup', 'pointercancel'].forEach((type) => canvas.addEventListener(type, () => {
            drawing = false;
            update();
        }));
        root.querySelector('[data-signature-clear]').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            dirty = false;
            update();
        });
        form?.querySelector('input[type=file]')?.addEventListener('change', update);
        window.addEventListener('resize', resize);
        resize();
    });
}
