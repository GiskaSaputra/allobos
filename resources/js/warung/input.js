// Pusat masukan: papan ketik, tetikus (pointer lock), gamepad, dan sentuh dipetakan ke aksi yang sama.
// Permainan hanya membaca aksi, tidak pernah membaca tombol mentah.

const KEYS = {
    KeyE: 'INTERACT', KeyF: 'PICK_UP', ShiftLeft: 'SPRINT', ShiftRight: 'SPRINT', Tab: 'INVENTORY', Escape: 'PAUSE', KeyP: 'PAUSE',
};
// Tata letak standar Gamepad API: 0 A/Silang, 1 B/Lingkaran, 2 X/Kotak, 3 Y/Segitiga, 6 LT, 7 RT, 8 Back, 9 Start, 10 L3.
const PAD = { 0: 'INTERACT', 2: 'PICK_UP', 7: 'USE', 6: 'INSPECT', 10: 'SPRINT', 3: 'INVENTORY', 9: 'PAUSE' };
const DEADZONE = 0.18;

const GLYPHS = {
    keyboard: { INTERACT: 'E', PICK_UP: 'F', USE: 'Klik', INSPECT: 'Klik kanan', SPRINT: 'Shift', INVENTORY: 'Tab', PAUSE: 'Esc' },
    xbox: { INTERACT: 'A', PICK_UP: 'X', USE: 'RT', INSPECT: 'LT', SPRINT: 'L3', INVENTORY: 'Y', PAUSE: 'Menu' },
    playstation: { INTERACT: 'Silang', PICK_UP: 'Kotak', USE: 'R2', INSPECT: 'L2', SPRINT: 'L3', INVENTORY: 'Segitiga', PAUSE: 'Options' },
    touch: { INTERACT: 'Interaksi', PICK_UP: 'Ambil', USE: 'Pakai', INSPECT: 'Lihat', SPRINT: 'Lari', INVENTORY: 'Isi', PAUSE: 'Jeda' },
};

const dead = (v) => (Math.abs(v) < DEADZONE ? 0 : (v - Math.sign(v) * DEADZONE) / (1 - DEADZONE));

export function createInput(target, { onDevice } = {}) {
    const held = new Set();
    const pressedNow = new Set();
    const look = { x: 0, y: 0 };
    const move = { x: 0, y: 0 };
    const keys = new Set();
    const touch = { move: { x: 0, y: 0 }, look: { x: 0, y: 0 } };
    let device = 'keyboard';
    let padIndex = null;
    let padKind = 'xbox';
    let padPrev = [];
    let enabled = true;

    function setDevice(next) {
        if (device === next) return;
        device = next;
        onDevice?.(next);
    }
    function press(action) {
        if (!held.has(action)) pressedNow.add(action);
        held.add(action);
    }

    window.addEventListener('keydown', (e) => {
        if (!enabled || e.target.closest?.('input, textarea, select')) return;
        setDevice('keyboard');
        keys.add(e.code);
        const action = KEYS[e.code];
        if (action) {
            if (action === 'INVENTORY') e.preventDefault();
            if (!e.repeat) press(action);
        }
    });
    window.addEventListener('keyup', (e) => {
        keys.delete(e.code);
        const action = KEYS[e.code];
        if (action) held.delete(action);
    });
    window.addEventListener('blur', () => { keys.clear(); held.clear(); });

    target.addEventListener('mousedown', (e) => {
        if (!enabled || document.pointerLockElement !== target) return;
        setDevice('keyboard');
        press(e.button === 2 ? 'INSPECT' : 'USE');
    });
    window.addEventListener('mouseup', (e) => held.delete(e.button === 2 ? 'INSPECT' : 'USE'));
    target.addEventListener('contextmenu', (e) => e.preventDefault());
    document.addEventListener('mousemove', (e) => {
        if (document.pointerLockElement !== target) return;
        look.x += e.movementX * 0.0022;
        look.y += e.movementY * 0.0022;
    });

    window.addEventListener('gamepadconnected', (e) => {
        padIndex = e.gamepad.index;
        padKind = /054c|playstation|dualsense|dualshock/i.test(e.gamepad.id) ? 'playstation' : 'xbox';
        padPrev = [];
        setDevice('gamepad');
    });
    window.addEventListener('gamepaddisconnected', (e) => {
        if (e.gamepad.index !== padIndex) return;
        padIndex = null;
        held.clear();
        setDevice('keyboard');
    });

    function pollPad() {
        if (padIndex === null) return null;
        const pad = navigator.getGamepads?.()[padIndex];
        if (!pad) return null;
        const buttons = pad.buttons.map((b) => b.pressed);
        buttons.forEach((down, i) => {
            const action = PAD[i];
            if (!action) return;
            if (down && !padPrev[i]) { press(action); setDevice('gamepad'); }
            if (!down && padPrev[i]) held.delete(action);
        });
        const edges = buttons.map((down, i) => down && !padPrev[i]);
        padPrev = buttons;
        const axes = pad.axes.map(dead);
        if (axes.some((a) => a !== 0)) setDevice('gamepad');
        return { axes, edges, buttons };
    }

    return {
        /** Dipanggil sekali per bingkai sebelum permainan membaca aksi. */
        update(dt) {
            const pad = pollPad();
            let mx = (keys.has('KeyD') || keys.has('ArrowRight') ? 1 : 0) - (keys.has('KeyA') || keys.has('ArrowLeft') ? 1 : 0);
            let my = (keys.has('KeyW') || keys.has('ArrowUp') ? 1 : 0) - (keys.has('KeyS') || keys.has('ArrowDown') ? 1 : 0);
            if (pad) {
                mx += pad.axes[0] || 0;
                my -= pad.axes[1] || 0;
                look.x += (pad.axes[2] || 0) * 2.6 * dt;
                look.y += (pad.axes[3] || 0) * 2.0 * dt;
            }
            mx += touch.move.x;
            my += touch.move.y;
            look.x += touch.look.x;
            look.y += touch.look.y;
            touch.look.x = touch.look.y = 0;
            const len = Math.hypot(mx, my);
            move.x = len > 1 ? mx / len : mx;
            move.y = len > 1 ? my / len : my;
            return pad;
        },
        /** Mengosongkan aksi sekali tekan di akhir bingkai. */
        endFrame() {
            pressedNow.clear();
            look.x = look.y = 0;
        },
        pressed: (action) => enabled && pressedNow.has(action),
        held: (action) => enabled && held.has(action),
        move,
        look,
        touch,
        get device() { return device; },
        glyph(action) {
            const set = device === 'gamepad' ? GLYPHS[padKind] : GLYPHS[device] || GLYPHS.keyboard;
            return set[action];
        },
        setEnabled(value) {
            enabled = value;
            if (!value) { held.clear(); pressedNow.clear(); move.x = move.y = 0; }
        },
        markTouch() { setDevice('touch'); },
        get hasPad() { return padIndex !== null; },
    };
}
