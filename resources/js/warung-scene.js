import * as THREE from 'three';

// Adegan low-poly dibangun dari geometri di kode, tanpa model atau tekstur luar.
function token(name, fallback) {
    const v = getComputedStyle(document.documentElement).getPropertyValue(`--${name}`).trim();
    if (!v) return new THREE.Color(fallback);
    const [r, g, b] = v.split(/\s+/).map(Number);
    return new THREE.Color(r / 255, g / 255, b / 255);
}

function textSprite(text, { color = 'rgb(20 26 12)', bg = 'rgb(205 224 72)', size = 64 } = {}) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    ctx.font = `700 ${size}px Montserrat Variable, sans-serif`;
    const w = Math.ceil(ctx.measureText(text).width) + size;
    canvas.width = w;
    canvas.height = size * 1.6;
    ctx.font = `700 ${size}px Montserrat Variable, sans-serif`;
    ctx.fillStyle = bg;
    ctx.beginPath();
    ctx.roundRect(0, 0, w, canvas.height, canvas.height / 2);
    ctx.fill();
    ctx.fillStyle = color;
    ctx.textBaseline = 'middle';
    ctx.fillText(text, size / 2, canvas.height / 2 + 2);
    const tex = new THREE.CanvasTexture(canvas);
    tex.colorSpace = THREE.SRGBColorSpace;
    const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: tex, transparent: true, depthWrite: false }));
    sprite.scale.set((w / canvas.height) * 0.5, 0.5, 1);
    return sprite;
}

function faceSprite(mood) {
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = 96;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = mood === 'senang' ? 'rgb(110 200 140)' : mood === 'ragu' ? 'rgb(232 180 80)' : 'rgb(240 120 100)';
    ctx.beginPath();
    ctx.arc(48, 48, 44, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = ctx.strokeStyle = 'rgb(20 26 12)';
    ctx.lineWidth = 6;
    ctx.beginPath(); ctx.arc(33, 40, 5, 0, Math.PI * 2); ctx.arc(63, 40, 5, 0, Math.PI * 2); ctx.fill();
    ctx.beginPath();
    if (mood === 'senang') ctx.arc(48, 52, 18, 0.15 * Math.PI, 0.85 * Math.PI);
    else if (mood === 'ragu') { ctx.moveTo(32, 66); ctx.lineTo(64, 62); }
    else ctx.arc(48, 76, 16, 1.15 * Math.PI, 1.85 * Math.PI);
    ctx.stroke();
    const tex = new THREE.CanvasTexture(canvas);
    tex.colorSpace = THREE.SRGBColorSpace;
    const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: tex, transparent: true, depthWrite: false }));
    sprite.scale.set(0.45, 0.45, 1);
    return sprite;
}

export async function createScene(canvas, { reduce }) {
    const colors = {
        brand: token('brand', '#3e4d1e'), accent: token('accent', '#cde048'), heroFrom: token('hero-from', '#8ba13a'),
        heroTo: token('hero-to', '#c7b92e'), ink: token('ink', '#0a0c07'), inkFg: token('ink-fg', '#f4fbc8'),
        mintFrom: token('mint-from', '#5ec4b6'), surface: token('surface-raised', '#f5f8ea'),
    };
    // Warna dari token desain, dicampur supaya adegan hangat dan tetap terbaca.
    const palette = {
        grass: colors.heroFrom.clone().lerp(colors.brand, 0.25),
        hill: colors.heroFrom.clone().lerp(colors.mintFrom, 0.35).lerp(colors.inkFg, 0.2),
        road: colors.ink.clone().lerp(colors.inkFg, 0.22),
        wall: colors.surface.clone().lerp(colors.heroTo, 0.15),
        roof: colors.heroTo.clone().lerp(new THREE.Color(0.75, 0.32, 0.2), 0.55),
        roofRival: colors.mintFrom.clone(),
        shelf: colors.heroTo.clone().lerp(colors.ink, 0.45),
        goods: [colors.accent.clone(), colors.heroTo.clone(), colors.mintFrom.clone(), colors.surface.clone()],
        skyDay: colors.mintFrom.clone().lerp(colors.inkFg, 0.55),
        skyDusk: colors.heroTo.clone().lerp(new THREE.Color(0.85, 0.45, 0.3), 0.6),
    };

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, powerPreference: 'low-power' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.outputColorSpace = THREE.SRGBColorSpace;

    const scene = new THREE.Scene();
    scene.background = palette.skyDay.clone();
    scene.fog = new THREE.Fog(scene.background, 26, 60);

    const camera = new THREE.PerspectiveCamera(42, 1, 0.1, 120);
    const camState = { angle: 0, zoom: 0, target: new THREE.Vector3(0, 1, 0) };

    const hemi = new THREE.HemisphereLight(palette.skyDay, palette.grass, 1.1);
    scene.add(hemi);
    const sun = new THREE.DirectionalLight(0xffffff, 1.6);
    sun.position.set(8, 14, 6);
    sun.castShadow = true;
    sun.shadow.mapSize.set(1024, 1024);
    sun.shadow.camera.left = -14; sun.shadow.camera.right = 14; sun.shadow.camera.top = 14; sun.shadow.camera.bottom = -14;
    sun.shadow.radius = 4;
    scene.add(sun);

    const mat = (color, extra = {}) => new THREE.MeshStandardMaterial({ color, roughness: 0.85, flatShading: true, ...extra });

    const ground = new THREE.Mesh(new THREE.PlaneGeometry(80, 80), mat(palette.grass));
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    scene.add(ground);

    const road = new THREE.Mesh(new THREE.PlaneGeometry(80, 4.2), mat(palette.road));
    road.rotation.x = -Math.PI / 2;
    road.position.set(0, 0.01, 4);
    road.receiveShadow = true;
    scene.add(road);
    const dashGeo = new THREE.PlaneGeometry(1.4, 0.16);
    const dashes = new THREE.InstancedMesh(dashGeo, mat(colors.inkFg), 24);
    const m4 = new THREE.Matrix4();
    for (let i = 0; i < 24; i++) {
        m4.makeRotationX(-Math.PI / 2).setPosition(-34 + i * 3, 0.02, 4);
        dashes.setMatrixAt(i, m4);
    }
    scene.add(dashes);

    // Bukit karst di latar
    const hillGeo = new THREE.IcosahedronGeometry(1, 0);
    const hills = new THREE.InstancedMesh(hillGeo, mat(palette.hill), 9);
    [[-18, -24, 4], [-9, -27, 5], [1, -28, 4.5], [11, -26, 5.5], [20, -23, 4], [-26, -18, 3.5], [27, -17, 3.5], [-4, -32, 6], [15, -32, 6]].forEach(([x, z, s], i) => {
        m4.compose(new THREE.Vector3(x, s * 0.2, z), new THREE.Quaternion().setFromEuler(new THREE.Euler(0, i, 0)), new THREE.Vector3(s, s * 0.75, s));
        hills.setMatrixAt(i, m4);
    });
    scene.add(hills);

    // Pohon dengan instancing
    const treeSpots = [[-7, -3], [-9, 1], [7.5, -2.5], [9, 1.5], [-5, -6], [5, -7], [-12, -4], [12, -5], [-3, 9], [6, 9], [-10, 9], [11, 8]];
    const trunks = new THREE.InstancedMesh(new THREE.CylinderGeometry(0.12, 0.16, 1, 6), mat(palette.shelf), treeSpots.length);
    const crowns = new THREE.InstancedMesh(new THREE.ConeGeometry(0.9, 2, 7), mat(palette.hill.clone().lerp(colors.accent, 0.15)), treeSpots.length);
    treeSpots.forEach(([x, z], i) => {
        const s = 0.8 + (i % 3) * 0.2;
        m4.compose(new THREE.Vector3(x, 0.5, z), new THREE.Quaternion(), new THREE.Vector3(1, 1, 1));
        trunks.setMatrixAt(i, m4);
        m4.compose(new THREE.Vector3(x, 1.8 * s, z), new THREE.Quaternion(), new THREE.Vector3(s, s, s));
        crowns.setMatrixAt(i, m4);
    });
    crowns.castShadow = true;
    scene.add(trunks, crowns);

    function buildWarung(roofColor) {
        const g = new THREE.Group();
        const wall = new THREE.Mesh(new THREE.BoxGeometry(4.2, 2, 2.8), mat(palette.wall));
        wall.position.y = 1;
        wall.castShadow = wall.receiveShadow = true;
        const roof = new THREE.Mesh(new THREE.ConeGeometry(3.6, 1.4, 4), mat(roofColor));
        roof.rotation.y = Math.PI / 4;
        roof.scale.set(1, 1, 0.78);
        roof.position.y = 2.7;
        roof.castShadow = true;
        const awning = new THREE.Mesh(new THREE.BoxGeometry(4.4, 0.1, 1.2), mat(roofColor));
        awning.position.set(0, 2.05, 1.9);
        awning.rotation.x = 0.25;
        awning.castShadow = true;
        const counter = new THREE.Mesh(new THREE.BoxGeometry(3.6, 0.9, 0.7), mat(palette.shelf));
        counter.position.set(0, 0.45, 1.6);
        counter.castShadow = true;
        g.add(wall, roof, awning, counter);
        return g;
    }

    const warung = buildWarung(palette.roof);
    scene.add(warung);

    // Rak dan isi rak: jumlah kotak barang mengikuti stok
    const shelf = new THREE.Mesh(new THREE.BoxGeometry(3.4, 0.08, 0.5), mat(palette.shelf));
    shelf.position.set(0, 1.25, 1.5);
    warung.add(shelf);
    const goodsMax = 48;
    const goods = new THREE.InstancedMesh(new THREE.BoxGeometry(0.22, 0.26, 0.22), mat(0xffffff), goodsMax);
    goods.castShadow = true;
    warung.add(goods);
    let shownGoods = 0;
    function setGoods(count) {
        shownGoods = Math.max(0, Math.min(goodsMax, count));
        for (let i = 0; i < goodsMax; i++) {
            const row = i < 24 ? 0 : 1;
            const col = i % 24;
            const visible = i < shownGoods;
            m4.compose(
                new THREE.Vector3(-1.55 + (col % 12) * 0.28, row === 0 ? 1.43 : 1.05, 1.42 + Math.floor(col / 12) * 0.2 - (row ? 0 : 0)),
                new THREE.Quaternion(),
                visible ? new THREE.Vector3(1, 1, 1) : new THREE.Vector3(0.0001, 0.0001, 0.0001),
            );
            goods.setMatrixAt(i, m4);
            goods.setColorAt(i, palette.goods[i % palette.goods.length]);
        }
        goods.instanceMatrix.needsUpdate = true;
        if (goods.instanceColor) goods.instanceColor.needsUpdate = true;
    }

    // Papan harga dengan tekstur kanvas
    const boardCanvas = document.createElement('canvas');
    boardCanvas.width = 512; boardCanvas.height = 256;
    const boardTex = new THREE.CanvasTexture(boardCanvas);
    boardTex.colorSpace = THREE.SRGBColorSpace;
    const board = new THREE.Mesh(new THREE.PlaneGeometry(1.8, 0.9), new THREE.MeshBasicMaterial({ map: boardTex }));
    board.position.set(2.75, 1.4, 1.7);
    board.rotation.y = -0.35;
    const post = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.05, 1.4, 6), mat(palette.shelf));
    post.position.set(2.75, 0.5, 1.68);
    warung.add(board, post);
    function drawBoard(items) {
        const ctx = boardCanvas.getContext('2d');
        ctx.fillStyle = 'rgb(24 27 18)';
        ctx.fillRect(0, 0, 512, 256);
        ctx.fillStyle = 'rgb(205 224 72)';
        ctx.font = '700 34px Montserrat Variable, sans-serif';
        ctx.fillText('HARGA HARI INI', 24, 44);
        ctx.font = '500 30px Ubuntu, sans-serif';
        ctx.fillStyle = 'rgb(244 251 200)';
        items.slice(0, 4).forEach((item, i) => {
            ctx.fillText(item.name.slice(0, 16), 24, 96 + i * 42);
            ctx.textAlign = 'right';
            ctx.fillText(`${(item.price / 1000).toLocaleString('id-ID')}rb`, 488, 96 + i * 42);
            ctx.textAlign = 'left';
        });
        boardTex.needsUpdate = true;
    }

    // Lampu warung, menyala saat senja
    const lamp = new THREE.PointLight(0xffd59a, 0, 9, 1.6);
    lamp.position.set(0, 2.1, 2.1);
    const bulb = new THREE.Mesh(new THREE.SphereGeometry(0.1, 8, 6), new THREE.MeshStandardMaterial({ color: 0xfff1c8, emissive: 0x000000 }));
    bulb.position.copy(lamp.position);
    warung.add(lamp, bulb);

    // Warung pesaing (babak 3 ke atas) dan bus (babak rombongan)
    const rival = buildWarung(palette.roofRival);
    rival.position.set(7, 0, 9.5);
    rival.rotation.y = Math.PI;
    rival.scale.setScalar(0.85);
    rival.visible = false;
    scene.add(rival);

    const bus = new THREE.Group();
    const busBody = new THREE.Mesh(new THREE.BoxGeometry(5.5, 1.8, 1.8), mat(colors.accent));
    busBody.position.y = 1.25;
    busBody.castShadow = true;
    const windows = new THREE.Mesh(new THREE.BoxGeometry(5.0, 0.5, 1.82), mat(colors.ink));
    windows.position.y = 1.6;
    bus.add(busBody, windows);
    [-1.8, 1.8].forEach((x) => [-0.9, 0.9].forEach((z) => {
        const wheel = new THREE.Mesh(new THREE.CylinderGeometry(0.35, 0.35, 0.25, 10), mat(colors.ink));
        wheel.rotation.x = Math.PI / 2;
        wheel.position.set(x, 0.35, z);
        bus.add(wheel);
    }));
    bus.position.set(-30, 0, 4.6);
    bus.visible = false;
    scene.add(bus);

    // Hujan
    const rainCount = reduce ? 0 : 1400;
    const rainGeo = new THREE.BufferGeometry();
    const rainPos = new Float32Array(rainCount * 3);
    for (let i = 0; i < rainCount; i++) {
        rainPos[i * 3] = (Math.random() - 0.5) * 40;
        rainPos[i * 3 + 1] = Math.random() * 18;
        rainPos[i * 3 + 2] = (Math.random() - 0.5) * 30;
    }
    rainGeo.setAttribute('position', new THREE.BufferAttribute(rainPos, 3));
    const rain = new THREE.Points(rainGeo, new THREE.PointsMaterial({ color: 0xdfe8ef, size: 0.07, transparent: true, opacity: 0.7 }));
    rain.visible = false;
    scene.add(rain);

    // Pelanggan dari kumpulan objek supaya tidak membuat ulang mesh
    const people = [];
    const bodyGeo = new THREE.CylinderGeometry(0.22, 0.28, 0.9, 7);
    const headGeo = new THREE.SphereGeometry(0.2, 8, 6);
    const shirtColors = [colors.mintFrom, colors.accent, colors.heroTo, new THREE.Color(0.85, 0.5, 0.45), colors.surface];
    for (let i = 0; i < 10; i++) {
        const p = new THREE.Group();
        const bodyMesh = new THREE.Mesh(bodyGeo, mat(shirtColors[i % shirtColors.length]));
        bodyMesh.position.y = 0.55;
        bodyMesh.castShadow = true;
        const head = new THREE.Mesh(headGeo, mat(new THREE.Color(0.78, 0.58, 0.42)));
        head.position.y = 1.2;
        head.castShadow = true;
        p.add(bodyMesh, head);
        p.visible = false;
        scene.add(p);
        people.push({ group: p, busy: false });
    }

    const floaters = [];
    function floatUp(sprite, from, duration = 1400) {
        sprite.position.copy(from);
        scene.add(sprite);
        floaters.push({ sprite, start: performance.now(), duration, y: from.y });
    }

    function burst() {
        if (reduce) return;
        for (let i = 0; i < 18; i++) {
            const bit = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.08, 0.08), new THREE.MeshBasicMaterial({ color: palette.goods[i % 4] }));
            bit.position.set(0, 2.6, 1.6);
            scene.add(bit);
            floaters.push({ sprite: bit, start: performance.now(), duration: 1200, y: 2.6, vx: (Math.random() - 0.5) * 3, vz: (Math.random() - 0.5) * 3, vy: 2 + Math.random() * 2, bit: true });
        }
    }

    let time = 0.25;
    function setTime(t) {
        time = t;
        const dusk = Math.max(0, (t - 0.6) / 0.4);
        const sky = palette.skyDay.clone().lerp(palette.skyDusk, dusk);
        scene.background.copy(sky);
        scene.fog.color.copy(sky);
        sun.intensity = 1.7 - dusk * 1.1;
        sun.color.setRGB(1, 1 - dusk * 0.25, 1 - dusk * 0.45);
        const a = Math.PI * (0.15 + t * 0.7);
        sun.position.set(Math.cos(a) * 14, Math.sin(a) * 14 + 2, 6);
        hemi.intensity = 1.1 - dusk * 0.5;
        lamp.intensity = dusk > 0.35 ? 3.2 * dusk : 0;
        bulb.material.emissive.setRGB(dusk > 0.35 ? 1 : 0, dusk > 0.35 ? 0.8 : 0, dusk > 0.35 ? 0.4 : 0);
    }
    setTime(0.25);

    function resize() {
        const { width, height } = canvas.getBoundingClientRect();
        if (!width || !height) return;
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
    }
    new ResizeObserver(resize).observe(canvas);

    function placeCamera(dt) {
        const radius = camState.zoom ? 9 : 14;
        const angle = camState.angle;
        const desired = new THREE.Vector3(Math.sin(angle) * radius, camState.zoom ? 6 : 9, Math.cos(angle) * radius + 2);
        const k = reduce ? 1 : Math.min(1, dt * 3);
        camera.position.lerp(desired, k);
        camera.lookAt(camState.target);
    }

    const clock = new THREE.Clock();
    let running = true;
    function frame() {
        const dt = clock.getDelta();
        placeCamera(dt);
        if (rain.visible) {
            const pos = rain.geometry.attributes.position;
            for (let i = 0; i < rainCount; i++) {
                let y = pos.getY(i) - dt * 14;
                if (y < 0) y = 18;
                pos.setY(i, y);
            }
            pos.needsUpdate = true;
        }
        const now = performance.now();
        for (let i = floaters.length - 1; i >= 0; i--) {
            const f = floaters[i];
            const t = (now - f.start) / f.duration;
            if (t >= 1) {
                scene.remove(f.sprite);
                f.sprite.material.map?.dispose();
                f.sprite.material.dispose();
                if (f.bit) f.sprite.geometry.dispose();
                floaters.splice(i, 1);
                continue;
            }
            if (f.bit) {
                f.sprite.position.x += f.vx * dt;
                f.sprite.position.z += f.vz * dt;
                f.sprite.position.y += (f.vy - t * 6) * dt;
            } else {
                f.sprite.position.y = f.y + t * 1.2;
                f.sprite.material.opacity = 1 - t;
            }
        }
        renderer.render(scene, camera);
    }
    renderer.setAnimationLoop(frame);

    // Render berhenti saat tab tidak aktif
    document.addEventListener('visibilitychange', () => {
        renderer.setAnimationLoop(document.hidden || !running ? null : frame);
    });

    const wait = (ms) => new Promise((r) => setTimeout(r, reduce ? Math.min(ms, 60) : ms));

    function walk(person, from, to, duration) {
        return new Promise((resolve) => {
            const start = performance.now();
            const tick = () => {
                const t = Math.min(1, (performance.now() - start) / (reduce ? 1 : duration));
                person.group.position.lerpVectors(from, to, t);
                person.group.position.y = Math.abs(Math.sin(t * 18)) * 0.06;
                person.group.lookAt(to.x, 0, to.z);
                if (t < 1) requestAnimationFrame(tick);
                else resolve();
            };
            tick();
        });
    }

    async function visitor(index, mood, amount, onSale) {
        const person = people[index % people.length];
        if (person.busy) return;
        person.busy = true;
        const side = index % 2 ? 1 : -1;
        const start = new THREE.Vector3(side * 16, 0, 4 + (index % 3) * 0.5);
        const shop = new THREE.Vector3((index % 3 - 1) * 0.9, 0, 2.6);
        person.group.position.copy(start);
        person.group.visible = true;
        await walk(person, start, shop, 1600);
        const face = faceSprite(mood);
        floatUp(face, shop.clone().add(new THREE.Vector3(0, 1.8, 0)), 1500);
        if (amount > 0) {
            floatUp(textSprite(`+Rp${amount.toLocaleString('id-ID')}`), new THREE.Vector3(shop.x, 2.4, shop.z), 1500);
            onSale?.();
        }
        await wait(500);
        await walk(person, shop, new THREE.Vector3(-side * 16, 0, 5), 1700);
        person.group.visible = false;
        person.busy = false;
    }

    function sync(view) {
        drawBoard(view.items);
        const stock = view.items.reduce((s, i) => s + i.stock, 0);
        setGoods(Math.ceil(stock / 3));
        rival.visible = view.flags.competitor;
        rain.visible = view.flags.rain && !reduce;
        if (view.phase === 'belanja') setTime(0.2);
        if (view.phase === 'laporan') setTime(0.95);
        camState.target.set(0, 1, view.phase === 'laporan' ? 1.5 : 0.5);
    }

    async function playDay(view, result, { onSale } = {}) {
        drawBoard(result.items.map((i) => ({ name: i.name, price: i.price })));
        if (view.flags.bus) {
            bus.visible = true;
            bus.position.set(-30, 0, 4.6);
            const start = performance.now();
            await new Promise((resolve) => {
                const tick = () => {
                    const t = Math.min(1, (performance.now() - start) / (reduce ? 1 : 2200));
                    bus.position.x = -30 + t * 26;
                    if (t < 1) requestAnimationFrame(tick); else resolve();
                };
                tick();
            });
        }

        const totalStock = view.items.reduce((s, i) => s + i.stock, 0);
        const buyers = Math.min(10, Math.max(1, Math.round(result.sold_total / 4)));
        const losers = result.lost_total > 0 ? Math.min(4, Math.ceil(result.lost_total / 6)) : 0;
        const perVisit = Math.round(result.revenue / buyers / 500) * 500;
        const steps = buyers + losers;
        const visits = [];
        for (let i = 0; i < steps; i++) {
            const t = 0.25 + (i / Math.max(1, steps)) * 0.7;
            setTime(t);
            const lost = i >= buyers;
            const mood = lost ? 'kecewa' : (result.satisfaction_after < result.satisfaction_before ? 'ragu' : 'senang');
            visits.push(visitor(i, mood, lost ? 0 : perVisit, onSale));
            const remaining = totalStock - Math.round((result.sold_total * Math.min(i + 1, buyers)) / buyers);
            setGoods(Math.ceil(Math.max(0, remaining) / 3));
            await wait(650);
        }
        await Promise.all(visits);
        setTime(0.95);
        if (result.hit_target) burst();
        if (view.flags.bus) bus.visible = false;
        await wait(600);
    }

    return {
        sync,
        playDay,
        camera(action) {
            if (action === 'left') camState.angle = Math.max(-Math.PI / 4, camState.angle - Math.PI / 8);
            if (action === 'right') camState.angle = Math.min(Math.PI / 4, camState.angle + Math.PI / 8);
            if (action === 'zoom') camState.zoom = camState.zoom ? 0 : 1;
        },
        pause() {
            running = false;
            renderer.setAnimationLoop(null);
        },
        resume() {
            running = true;
            resize();
            renderer.setAnimationLoop(frame);
        },
    };
}
