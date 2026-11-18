import * as THREE from 'three';
import { createInput } from './input';
import { createPlayer } from './player';
import { createWorld } from './world';
import { createInteraction } from './interact';
import { createCustomers } from './customers';

/**
 * Lapisan 3D orang pertama. Tidak menyimpan aturan bisnis: semua keputusan dikirim lewat hooks
 * ke lapisan antarmuka, yang meneruskannya ke server.
 */
export async function createGame(canvas, { reduce, poseKey, touchRoot, hooks }) {
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.outputColorSpace = THREE.SRGBColorSpace;

    const camera = new THREE.PerspectiveCamera(75, 1, 0.05, 140);
    const world = createWorld({ reduce });
    const player = createPlayer(camera, { colliders: world.colliders, bounds: world.bounds, reduce });
    world.scene.add(player.body);
    player.setPose(readPose() ?? { x: 0, z: -1.2, yaw: Math.PI, pitch: -0.08 });

    const input = createInput(canvas, { onDevice: (d) => hooks.device?.(d) });
    const interaction = createInteraction(camera, world);
    const names = {};
    const customers = createCustomers(world, {
        reduce,
        names,
        onMiss: (id) => hooks.miss(id),
        onArrive: (npc) => hooks.arrive?.(npc),
    });

    // Aset luar dimuat di belakang. Permainan sudah bisa dimainkan dengan dunia dasar sambil menunggu.
    import('./assets').then(({ loadAssets }) => loadAssets(renderer)).then((assets) => {
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 0.85;
        world.dress(assets);
        customers.useAssets(assets);
        hooks.assets?.(true);
    }).catch(() => hooks.assets?.(false));

    let active = true;
    let running = true;
    let serving = false;
    let lastPrompt = '';
    let poseTimer = 0;

    function readPose() {
        try {
            const raw = localStorage.getItem(poseKey);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }
    function savePose() {
        try { localStorage.setItem(poseKey, JSON.stringify(player.pose())); } catch (e) { /* penyimpanan lokal tidak tersedia */ }
    }

    function resize() {
        const { width, height } = canvas.getBoundingClientRect();
        if (!width || !height) return;
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
    }
    new ResizeObserver(resize).observe(canvas);

    // Pointer lock hanya untuk tetikus. Lepas kunci di luar panel berarti pemain menekan Esc: jeda.
    canvas.addEventListener('click', () => {
        if (active && input.device !== 'touch' && document.pointerLockElement !== canvas) canvas.requestPointerLock?.();
    });
    document.addEventListener('pointerlockchange', () => {
        if (!document.pointerLockElement && active && input.device === 'keyboard') hooks.pause();
    });

    setupTouch();

    function prompt(target) {
        const held = interaction.held;
        const key = input.glyph('INTERACT');
        if (held) {
            const name = names[held.userData.item];
            if (target?.userData.kind === 'zone') {
                return target.userData.item === held.userData.item
                    ? `${key}: Taruh peti ${name} di rak`
                    : `Rak ini untuk ${names[target.userData.item]}. Cari rak ${name}.`;
            }
            return `${input.glyph('PICK_UP')}: Kembalikan peti. ${input.glyph('INSPECT')} tahan: amati`;
        }
        if (!target) return '';
        if (target.userData.kind === 'zone') return '';
        if (target.userData.kind === 'crate') return `${key}: ${target.userData.label}`;
        const label = hooks.label(target.userData);
        return label ? `${key}: ${label}` : '';
    }

    async function useTarget(target) {
        const held = interaction.held;
        if (held) {
            if (target?.userData.kind === 'zone' && target.userData.item === held.userData.item && !serving) {
                serving = true;
                const ok = await hooks.place(held.userData.item);
                serving = false;
                if (ok) interaction.place();
            }
            return;
        }
        if (!target) return;
        if (target.userData.kind === 'crate') {
            if (hooks.canPick(target.userData.item)) interaction.pickUp(target);
            return;
        }
        if (target.userData.kind === 'register') {
            const npc = customers.front();
            if (!npc || serving) { hooks.station('register', null); return; }
            serving = true;
            const receipt = await hooks.serve(npc.data.id);
            serving = false;
            if (receipt) customers.paid(npc, receipt);
            return;
        }
        hooks.station(target.userData.kind, target.userData);
    }

    const clock = new THREE.Clock();
    function frame() {
        const dt = Math.min(0.05, clock.getDelta());
        const pad = input.update(dt);
        if (active) {
            const inspecting = interaction.inspect(input.held('INSPECT'), input.look);
            player.update(dt, input, { lookLocked: inspecting });
            const target = interaction.update();
            const text = prompt(target);
            if (text !== lastPrompt) { lastPrompt = text; hooks.prompt(text); }

            if (input.pressed('INTERACT') || input.pressed('USE')) useTarget(target);
            if (input.pressed('PICK_UP')) {
                if (interaction.held) interaction.drop();
                else if (target?.userData.kind === 'crate') useTarget(target);
            }
            if (input.pressed('INVENTORY')) hooks.inventory();
            if (input.pressed('PAUSE')) hooks.pause();

            poseTimer += dt;
            if (poseTimer > 2) { poseTimer = 0; savePose(); }
        } else if (pad) {
            hooks.padUi(pad);
        }
        customers.update(dt);
        world.tick(dt);
        renderer.render(world.scene, camera);
        input.endFrame();
    }
    renderer.setAnimationLoop(frame);
    document.addEventListener('visibilitychange', () => {
        renderer.setAnimationLoop(document.hidden || !running ? null : frame);
    });

    function setupTouch() {
        if (!touchRoot) return;
        const stick = touchRoot.querySelector('[data-touch-stick]');
        const knob = touchRoot.querySelector('[data-touch-knob]');
        const lookArea = touchRoot.querySelector('[data-touch-look]');
        let stickId = null;
        let lookId = null;
        let last = null;
        stick.addEventListener('pointerdown', (e) => { stickId = e.pointerId; stick.setPointerCapture(e.pointerId); input.markTouch(); moveStick(e); });
        stick.addEventListener('pointermove', (e) => { if (e.pointerId === stickId) moveStick(e); });
        const endStick = (e) => {
            if (e.pointerId !== stickId) return;
            stickId = null;
            input.touch.move.x = input.touch.move.y = 0;
            knob.style.transform = '';
        };
        stick.addEventListener('pointerup', endStick);
        stick.addEventListener('pointercancel', endStick);
        function moveStick(e) {
            const r = stick.getBoundingClientRect();
            const dx = (e.clientX - (r.left + r.width / 2)) / (r.width / 2);
            const dy = (e.clientY - (r.top + r.height / 2)) / (r.height / 2);
            const len = Math.min(1, Math.hypot(dx, dy)) || 0;
            const a = Math.atan2(dy, dx);
            input.touch.move.x = Math.cos(a) * len;
            input.touch.move.y = -Math.sin(a) * len;
            knob.style.transform = `translate(${Math.cos(a) * len * 28}px, ${Math.sin(a) * len * 28}px)`;
        }
        lookArea.addEventListener('pointerdown', (e) => { lookId = e.pointerId; last = [e.clientX, e.clientY]; lookArea.setPointerCapture(e.pointerId); input.markTouch(); });
        lookArea.addEventListener('pointermove', (e) => {
            if (e.pointerId !== lookId) return;
            input.touch.look.x += (e.clientX - last[0]) * 0.005;
            input.touch.look.y += (e.clientY - last[1]) * 0.005;
            last = [e.clientX, e.clientY];
        });
        lookArea.addEventListener('pointerup', () => { lookId = null; });
        touchRoot.querySelectorAll('[data-touch-action]').forEach((btn) => {
            btn.addEventListener('pointerdown', (e) => {
                e.preventDefault();
                input.markTouch();
                const action = btn.dataset.touchAction;
                if (action === 'PAUSE') { hooks.pause(); return; }
                if (action === 'PICK_UP') {
                    const target = interaction.update();
                    if (interaction.held) interaction.drop();
                    else if (target?.userData.kind === 'crate') useTarget(target);
                    return;
                }
                useTarget(interaction.update());
            });
        });
    }

    return {
        input,
        sync(view) {
            view.items.forEach((i) => { names[i.key] = i.name; });
            world.sync(view);
            world.setNotice(`Hari ${view.day}: ${view.theme.title}`, [view.event?.title ?? view.theme.lesson, ...hooks.objectives(view)]);
            if (view.phase !== 'melayani') customers.clear();
        },
        startServing(view) {
            customers.load(view.customers, { bus: view.flags.bus });
            if (view.flags.bus) world.driveBus();
        },
        setTime: (t) => world.setTime(t),
        remaining: () => customers.remaining(),
        /** Panel terbuka: masukan permainan dimatikan dan kunci tetikus dilepas. */
        setActive(value) {
            active = value;
            input.setEnabled(value);
            if (!value) {
                interaction.update();
                if (document.pointerLockElement === canvas) document.exitPointerLock();
            }
        },
        lock() {
            if (input.device !== 'touch' && input.device !== 'gamepad') canvas.requestPointerLock?.();
        },
        get holding() { return interaction.held?.userData.item ?? null; },
        pause() {
            running = false;
            savePose();
            renderer.setAnimationLoop(null);
        },
        resume() {
            running = true;
            resize();
            clock.getDelta();
            renderer.setAnimationLoop(frame);
        },
    };
}
