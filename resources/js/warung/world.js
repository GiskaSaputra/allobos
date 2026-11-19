import * as THREE from 'three';

// Dunia warung dibangun dari geometri di kode sebagai dasar dan cadangan. Setelah aset CC0 termuat, dress() mengganti
// langit, alam, perabot, barang di rak, dan tokoh. Tabrakan dan titik interaksi tetap dari geometri dasar.
// Tata letak (meter): bagian dalam x -5..5, z -4..3. Meja depan di z 3, jalan di z 9.

function token(name, fallback) {
    const v = getComputedStyle(document.documentElement).getPropertyValue(`--${name}`).trim();
    if (!v) return new THREE.Color(fallback);
    const [r, g, b] = v.split(/\s+/).map(Number);
    return new THREE.Color(r / 255, g / 255, b / 255);
}

/** Papan tulisan dari kanvas. draw(ctx, w, h) dipanggil ulang lewat redraw(). */
export function canvasPlane(width, height, draw, px = 256) {
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(px * width);
    canvas.height = Math.round(px * height);
    const tex = new THREE.CanvasTexture(canvas);
    tex.colorSpace = THREE.SRGBColorSpace;
    const mesh = new THREE.Mesh(new THREE.PlaneGeometry(width, height), new THREE.MeshBasicMaterial({ map: tex, transparent: true }));
    mesh.userData.redraw = (...args) => {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        draw(ctx, canvas.width, canvas.height, ...args);
        tex.needsUpdate = true;
    };
    mesh.userData.redraw();
    return mesh;
}

export function roundRect(ctx, x, y, w, h, r, fill) {
    ctx.fillStyle = fill;
    ctx.beginPath();
    ctx.roundRect(x, y, w, h, r);
    ctx.fill();
}

/** Memecah teks menjadi baris yang muat di lebar tertentu. */
function wrap(ctx, text, width) {
    const rows = [];
    let row = '';
    text.split(' ').forEach((word) => {
        const next = row ? `${row} ${word}` : word;
        if (ctx.measureText(next).width > width && row) { rows.push(row); row = `  ${word}`; } else row = next;
    });
    return row ? [...rows, row] : rows;
}

const INK = 'rgb(24 27 18)';
const LIME = 'rgb(205 224 72)';
const CREAM = 'rgb(244 251 200)';

export function createWorld({ reduce }) {
    const c = {
        brand: token('brand', '#3e4d1e'), accent: token('accent', '#cde048'), heroFrom: token('hero-from', '#8ba13a'),
        heroTo: token('hero-to', '#c7b92e'), ink: token('ink', '#0a0c07'), inkFg: token('ink-fg', '#f4fbc8'),
        mint: token('mint-from', '#5ec4b6'),
    };
    const p = {
        grass: c.heroFrom.clone().lerp(c.brand, 0.25),
        hill: c.heroFrom.clone().lerp(c.mint, 0.35).lerp(c.inkFg, 0.2),
        road: c.ink.clone().lerp(c.inkFg, 0.22),
        wall: new THREE.Color(0.93, 0.9, 0.8),
        floor: new THREE.Color(0.62, 0.5, 0.38),
        wood: new THREE.Color(0.48, 0.33, 0.2),
        woodDark: new THREE.Color(0.32, 0.22, 0.14),
        roof: c.heroTo.clone().lerp(new THREE.Color(0.75, 0.32, 0.2), 0.55),
        skyDay: c.mint.clone().lerp(c.inkFg, 0.55),
        skyDusk: c.heroTo.clone().lerp(new THREE.Color(0.85, 0.45, 0.3), 0.6),
        goods: [c.accent, c.heroTo, c.mint, new THREE.Color(0.9, 0.55, 0.45)],
    };
    const mat = (color, extra = {}) => new THREE.MeshStandardMaterial({ color, roughness: 0.85, flatShading: true, ...extra });

    const scene = new THREE.Scene();
    scene.background = p.skyDay.clone();
    scene.fog = new THREE.Fog(scene.background, 22, 70);
    const hemi = new THREE.HemisphereLight(p.skyDay, p.grass, 1.0);
    const sun = new THREE.DirectionalLight(0xffffff, 1.5);
    sun.castShadow = true;
    sun.shadow.mapSize.set(1024, 1024);
    Object.assign(sun.shadow.camera, { left: -16, right: 16, top: 16, bottom: -16 });
    const indoor = new THREE.PointLight(0xfff0d0, 6, 12, 1.4);
    indoor.position.set(0, 2.7, -0.5);
    scene.add(hemi, sun, sun.target, indoor);

    const colliders = [];
    const solids = [];
    const interactables = [];
    const box = (w, h, d, color, x, y, z, { collide = false, shadow = true, parent = scene } = {}) => {
        const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat(color));
        m.position.set(x, y, z);
        m.castShadow = shadow;
        m.receiveShadow = true;
        parent.add(m);
        solids.push(m);
        if (collide) colliders.push({ minX: x - w / 2, maxX: x + w / 2, minZ: z - d / 2, maxZ: z + d / 2 });
        return m;
    };
    /** Menandai kelompok sebagai benda yang bisa diajak berinteraksi. Materialnya disalin supaya sorotan tidak menular. */
    const interactive = (group, data) => {
        group.userData = { ...group.userData, interactive: true, ...data };
        group.traverse((o) => { if (o.isMesh && o.material.emissive) o.material = o.material.clone(); });
        interactables.push(group);
        return group;
    };

    // Tanah, jalan, bukit karst, pepohonan
    const ground = new THREE.Mesh(new THREE.PlaneGeometry(120, 120), mat(p.grass));
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    scene.add(ground);
    const road = new THREE.Mesh(new THREE.PlaneGeometry(120, 4.4), mat(p.road));
    road.rotation.x = -Math.PI / 2;
    road.position.set(0, 0.01, 9);
    scene.add(road);
    const m4 = new THREE.Matrix4();
    const hills = new THREE.InstancedMesh(new THREE.IcosahedronGeometry(1, 0), mat(p.hill), 10);
    [[-22, 30, 5], [-10, 34, 6], [4, 36, 5], [16, 33, 6.5], [28, 29, 5], [-30, 22, 4], [34, 20, 4], [-24, -24, 6], [6, -28, 7], [26, -22, 6]].forEach(([x, z, s], i) => {
        m4.compose(new THREE.Vector3(x, s * 0.2, z), new THREE.Quaternion().setFromEuler(new THREE.Euler(0, i, 0)), new THREE.Vector3(s, s * 0.75, s));
        hills.setMatrixAt(i, m4);
    });
    scene.add(hills);
    const treeSpots = [[-9, 6], [-12, 3], [9, 5.5], [12, 2], [-8, -7], [8, -7], [-14, -2], [14, -3], [-6, 13], [7, 14], [-15, 13], [16, 12]];
    const trunks = new THREE.InstancedMesh(new THREE.CylinderGeometry(0.14, 0.18, 1.2, 6), mat(p.woodDark), treeSpots.length);
    const crowns = new THREE.InstancedMesh(new THREE.ConeGeometry(1, 2.4, 7), mat(p.hill.clone().lerp(c.accent, 0.15)), treeSpots.length);
    treeSpots.forEach(([x, z], i) => {
        const s = 0.9 + (i % 3) * 0.25;
        m4.compose(new THREE.Vector3(x, 0.6, z), new THREE.Quaternion(), new THREE.Vector3(1, 1, 1));
        trunks.setMatrixAt(i, m4);
        m4.compose(new THREE.Vector3(x, 2.2 * s, z), new THREE.Quaternion(), new THREE.Vector3(s, s, s));
        crowns.setMatrixAt(i, m4);
    });
    crowns.castShadow = true;
    scene.add(trunks, crowns);

    // Bangunan warung: lantai, dinding belakang dan samping, atap, meja depan
    const floor = new THREE.Mesh(new THREE.PlaneGeometry(10.4, 7.6), mat(p.floor));
    floor.rotation.x = -Math.PI / 2;
    floor.position.set(0, 0.02, -0.5);
    floor.receiveShadow = true;
    scene.add(floor);
    box(10.4, 3.2, 0.2, p.wall, 0, 1.6, -4.2, { collide: true });
    box(0.2, 3.2, 7.6, p.wall, -5.2, 1.6, -0.5, { collide: true });
    box(0.2, 3.2, 4.6, p.wall, 5.2, 1.6, -2, { collide: true });
    box(0.2, 3.2, 1.4, p.wall, 5.2, 1.6, 2.6, { collide: true });
    box(10.8, 0.15, 8.6, p.roof, 0, 3.25, -0.4, { shadow: true });
    const awning = box(10.8, 0.1, 1.8, p.roof, 0, 2.9, 3.9);
    awning.rotation.x = 0.22;
    box(0.15, 2.9, 0.15, p.woodDark, -5.1, 1.45, 4.6);
    box(0.15, 2.9, 0.15, p.woodDark, 5.1, 1.45, 4.6);
    box(10, 1.0, 0.7, p.wood, 0, 0.5, 3, { collide: true });
    box(10.2, 0.08, 0.85, p.woodDark, 0, 1.04, 3);

    // Pintu samping untuk pemasok, tertutup tirai
    const curtain = box(0.05, 2.2, 1.4, c.mint.clone().lerp(p.wall, 0.3), 5.15, 1.1, 1.2, { shadow: false });
    curtain.material.transparent = true;
    curtain.material.opacity = 0.85;
    colliders.push({ minX: 5.1, maxX: 5.4, minZ: 0.5, maxZ: 1.9 });

    // Stasiun 1: meja pemasok (belanja)
    const supply = new THREE.Group();
    supply.position.set(3.9, 0, -0.2);
    box(1.6, 0.9, 1, p.wood, 0, 0.45, 0, { parent: supply });
    box(0.5, 0.35, 0.4, p.goods[1], -0.4, 1.08, 0, { parent: supply });
    box(0.45, 0.3, 0.35, p.goods[0], 0.15, 1.05, 0.1, { parent: supply });
    const clipboard = canvasPlane(0.42, 0.55, (ctx, w, h) => {
        roundRect(ctx, 0, 0, w, h, 12, CREAM);
        ctx.fillStyle = INK;
        ctx.font = `700 ${w * 0.12}px Montserrat Variable, sans-serif`;
        ctx.fillText('PEMASOK', w * 0.1, h * 0.18);
        for (let i = 0; i < 5; i++) ctx.fillRect(w * 0.1, h * (0.32 + i * 0.12), w * (0.5 + (i % 2) * 0.25), 6);
    });
    clipboard.rotation.x = -Math.PI / 2 + 0.3;
    clipboard.position.set(0.55, 0.93, 0.1);
    supply.add(clipboard);
    scene.add(supply);
    colliders.push({ minX: 3.1, maxX: 4.7, minZ: -0.7, maxZ: 0.3 });
    interactive(supply, { kind: 'supply', label: 'Belanja di meja pemasok' });

    // Stasiun 2: rak gudang dengan peti per barang
    const storage = new THREE.Group();
    storage.position.set(-2.6, 0, -3.65);
    box(4.2, 0.08, 0.8, p.woodDark, 0, 0.45, 0, { parent: storage });
    box(4.2, 0.08, 0.8, p.woodDark, 0, 1.35, 0, { parent: storage });
    [-2.05, 2.05].forEach((x) => box(0.08, 2.2, 0.8, p.woodDark, x, 1.1, 0, { parent: storage }));
    scene.add(storage);
    colliders.push({ minX: -4.75, maxX: -0.45, minZ: -4.1, maxZ: -3.2 });

    // Stasiun 3: etalase pajang di meja depan, satu zona per barang
    const shelf = new THREE.Group();
    shelf.position.set(2.6, 1.08, 3);
    box(4.4, 0.06, 0.62, p.woodDark, 0, 0, 0, { parent: shelf, shadow: false });
    const glass = new THREE.Mesh(new THREE.BoxGeometry(4.4, 0.55, 0.62), new THREE.MeshStandardMaterial({ color: 0xdff4ff, transparent: true, opacity: 0.18, roughness: 0.1 }));
    glass.position.y = 0.3;
    shelf.add(glass);
    scene.add(shelf);

    // Stasiun 4: papan harga di dinding kiri
    const priceBoard = new THREE.Group();
    priceBoard.position.set(-5.08, 1.6, 0.2);
    priceBoard.rotation.y = Math.PI / 2;
    const priceFace = canvasPlane(2.2, 1.3, (ctx, w, h, items = []) => {
        roundRect(ctx, 0, 0, w, h, 24, INK);
        ctx.fillStyle = LIME;
        ctx.font = `700 ${h * 0.12}px Montserrat Variable, sans-serif`;
        ctx.fillText('HARGA HARI INI', w * 0.06, h * 0.17);
        ctx.font = `500 ${h * 0.1}px Ubuntu, sans-serif`;
        items.slice(0, 4).forEach((item, i) => {
            ctx.fillStyle = CREAM;
            ctx.textAlign = 'left';
            ctx.fillText(item.name, w * 0.06, h * (0.38 + i * 0.17));
            ctx.textAlign = 'right';
            ctx.fillText(`Rp${item.price.toLocaleString('id-ID')}`, w * 0.94, h * (0.38 + i * 0.17));
        });
        ctx.textAlign = 'left';
    });
    priceBoard.add(priceFace);
    box(2.35, 1.45, 0.06, p.woodDark, 0, 0, -0.04, { parent: priceBoard, shadow: false });
    scene.add(priceBoard);
    interactive(priceBoard, { kind: 'price', label: 'Atur harga di papan harga' });

    // Stasiun 5: papan pengumuman di dinding belakang (kejadian, promosi, keputusan, tujuan)
    const notice = new THREE.Group();
    notice.position.set(2.4, 1.7, -4.08);
    const noticeFace = canvasPlane(1.9, 1.3, (ctx, w, h, title = 'Papan pengumuman', lines = []) => {
        roundRect(ctx, 0, 0, w, h, 20, 'rgb(196 160 112)');
        roundRect(ctx, w * 0.05, h * 0.07, w * 0.9, h * 0.86, 14, CREAM);
        ctx.fillStyle = INK;
        // Judul panjang dikecilkan sampai muat di dalam bingkai.
        ctx.font = `700 ${h * 0.1}px Montserrat Variable, sans-serif`;
        const fit = (w * 0.8) / ctx.measureText(title).width;
        if (fit < 1) ctx.font = `700 ${h * 0.1 * fit}px Montserrat Variable, sans-serif`;
        ctx.fillText(title, w * 0.1, h * 0.22);
        ctx.font = `500 ${h * 0.075}px Ubuntu, sans-serif`;
        const rows = lines.flatMap((line) => wrap(ctx, `- ${line}`, w * 0.8)).slice(0, 5);
        rows.forEach((row, i) => ctx.fillText(row, w * 0.1, h * (0.38 + i * 0.12)));
    });
    notice.add(noticeFace);
    scene.add(notice);
    interactive(notice, { kind: 'notice', label: 'Baca papan pengumuman' });

    // Stasiun 6: papan BUKA/TUTUP di ujung kiri meja depan
    const sign = new THREE.Group();
    sign.position.set(-4.4, 1.08, 2.95);
    box(0.08, 0.5, 0.08, p.woodDark, 0, 0.25, 0, { parent: sign, shadow: false });
    const signFace = canvasPlane(0.9, 0.5, (ctx, w, h, open = false) => {
        roundRect(ctx, 0, 0, w, h, 18, open ? LIME : 'rgb(180 52 36)');
        ctx.fillStyle = open ? INK : CREAM;
        ctx.font = `700 ${h * 0.45}px Montserrat Variable, sans-serif`;
        ctx.textAlign = 'center';
        ctx.fillText(open ? 'BUKA' : 'TUTUP', w / 2, h * 0.66);
    });
    signFace.position.y = 0.7;
    const signBack = signFace.clone();
    signBack.rotation.y = Math.PI;
    sign.add(signFace, signBack);
    scene.add(sign);
    interactive(sign, { kind: 'sign', label: 'Balik papan buka' });

    // Stasiun 7: mesin kasir
    const register = new THREE.Group();
    register.position.set(-1.6, 1.08, 2.9);
    box(0.6, 0.3, 0.45, c.ink.clone().lerp(p.woodDark, 0.4), 0, 0.15, 0, { parent: register });
    const screen = box(0.4, 0.22, 0.04, c.accent, 0, 0.42, -0.08, { parent: register, shadow: false });
    screen.rotation.x = -0.4;
    box(0.5, 0.06, 0.3, c.inkFg.clone().lerp(p.wood, 0.5), 0, 0.33, 0.06, { parent: register, shadow: false });
    scene.add(register);
    interactive(register, { kind: 'register', label: 'Layani pembeli di kasir' });

    // Warung pesaing di seberang jalan dan bus rombongan
    const rival = new THREE.Group();
    box(4, 2.2, 3, c.mint.clone().lerp(p.wall, 0.4), 0, 1.1, 0, { parent: rival });
    box(4.4, 0.15, 3.4, c.mint, 0, 2.3, 0, { parent: rival });
    const rivalSign = canvasPlane(2.2, 0.5, (ctx, w, h) => {
        roundRect(ctx, 0, 0, w, h, 16, CREAM);
        ctx.fillStyle = INK;
        ctx.font = `700 ${h * 0.42}px Montserrat Variable, sans-serif`;
        ctx.textAlign = 'center';
        ctx.fillText('SERBA MURAH', w / 2, h * 0.65);
    });
    rivalSign.position.set(0, 1.8, -1.52);
    rivalSign.rotation.y = Math.PI;
    rival.add(rivalSign);
    rival.position.set(6, 0, 14.5);
    rival.visible = false;
    scene.add(rival);

    const bus = new THREE.Group();
    box(6, 2, 2, c.accent, 0, 1.3, 0, { parent: bus });
    box(5.5, 0.55, 2.02, c.ink, 0, 1.7, 0, { parent: bus, shadow: false });
    [-2, 2].forEach((x) => [-1, 1].forEach((z) => {
        const wheel = new THREE.Mesh(new THREE.CylinderGeometry(0.38, 0.38, 0.25, 12), mat(c.ink));
        wheel.rotation.x = Math.PI / 2;
        wheel.position.set(x, 0.38, z);
        bus.add(wheel);
    }));
    bus.position.set(-40, 0, 9.6);
    bus.visible = false;
    scene.add(bus);

    // Hujan
    const rainCount = reduce ? 0 : 1600;
    const rainPos = new Float32Array(rainCount * 3);
    for (let i = 0; i < rainCount; i++) {
        rainPos.set([(Math.random() - 0.5) * 40, Math.random() * 16, (Math.random() - 0.5) * 30 + 6], i * 3);
    }
    const rainGeo = new THREE.BufferGeometry();
    rainGeo.setAttribute('position', new THREE.BufferAttribute(rainPos, 3));
    const rain = new THREE.Points(rainGeo, new THREE.PointsMaterial({ color: 0xdfe8ef, size: 0.06, transparent: true, opacity: 0.7 }));
    rain.visible = false;
    scene.add(rain);

    // Peti di gudang dan zona pajang dibuat per barang saat data warung pertama tiba.
    const crates = {};
    const zones = {};
    const crateGeo = new THREE.BoxGeometry(0.7, 0.4, 0.5);
    const goodGeo = new THREE.BoxGeometry(0.13, 0.16, 0.13);
    function labelPlane(text, w = 0.62, h = 0.16) {
        return canvasPlane(w, h, (ctx, cw, ch, t = text) => {
            roundRect(ctx, 0, 0, cw, ch, ch / 2, CREAM);
            ctx.fillStyle = INK;
            ctx.font = `700 ${ch * 0.5}px Montserrat Variable, sans-serif`;
            const fit = (cw * 0.88) / ctx.measureText(t).width;
            if (fit < 1) ctx.font = `700 ${ch * 0.5 * fit}px Montserrat Variable, sans-serif`;
            ctx.textAlign = 'center';
            ctx.fillText(t, cw / 2, ch * 0.68);
        });
    }
    function setupItems(items) {
        if (Object.keys(crates).length) return;
        items.forEach((item, i) => {
            const color = p.goods[i % p.goods.length];
            const crate = new THREE.Group();
            const shell = new THREE.Mesh(crateGeo, mat(p.wood));
            shell.castShadow = true;
            const fill = new THREE.Mesh(new THREE.BoxGeometry(0.6, 0.06, 0.4), mat(color));
            fill.position.y = 0.2;
            const label = labelPlane(item.name);
            label.position.set(0, 0.02, 0.255);
            crate.add(shell, fill, label);
            const home = new THREE.Vector3(-1.5 + (i % 4) * 1.0, i < 4 ? 0.72 : 1.62, 0.05);
            crate.position.copy(home);
            storage.add(crate);
            interactive(crate, { kind: 'crate', item: item.key, label: `Ambil peti ${item.name}`, home, homeParent: storage, state: 'WORLD' });
            crates[item.key] = crate;

            const zone = new THREE.Group();
            zone.userData.qty = 0;
            zone.position.set(-1.65 + i * 1.1, 0.04, 0);
            const pad = new THREE.Mesh(new THREE.BoxGeometry(1.0, 0.02, 0.56), mat(color.clone().lerp(c.inkFg, 0.6)));
            const goods = new THREE.InstancedMesh(goodGeo, mat(color), 40);
            goods.count = 0;
            goods.frustumCulled = false;
            const tag = labelPlane(item.name, 0.9, 0.13);
            tag.position.set(0, -0.08, 0.32);
            const tagBack = tag.clone();
            tagBack.rotation.y = Math.PI;
            tagBack.position.z = -0.32;
            zone.add(pad, goods, tag, tagBack);
            zone.userData.goods = goods;
            shelf.add(zone);
            interactive(zone, { kind: 'zone', item: item.key, label: `Rak ${item.name}` });
            zones[item.key] = zone;
        });
        if (assets) dressGoods();
    }
    function setShelf(key, qty) {
        const zone = zones[key];
        const goods = zone?.userData.goods;
        if (!goods) return;
        zone.userData.qty = qty;
        // Geometri dasar berpusat di tengah kotak, geometri model beralas di nol.
        const base = zone.userData.goodsBase ?? 0.1;
        const layer = zone.userData.goodsHeight ?? 0.17;
        goods.count = Math.min(40, qty);
        for (let i = 0; i < goods.count; i++) {
            m4.makeTranslation(-0.4 + (i % 6) * 0.16, base + Math.floor(i / 18) * layer, -0.16 + (Math.floor(i / 6) % 3) * 0.16);
            goods.setMatrixAt(i, m4);
        }
        goods.instanceMatrix.needsUpdate = true;
    }

    // Model barang per kunci barang di GameRules. Barang tanpa padanan tetap memakai kotak warna.
    const GOODS_MODEL = {
        keripik: 'bag-flat', tiwul: 'rice-ball', gethuk: 'pudding', es_teh: 'cup-tea', air: 'soda-bottle', mi: 'chinese',
        roti: 'loaf', telur: 'egg', belalang: 'honey', peyek: 'cookie', kaos: 'bag', topeng: 'carton-small',
    };
    let assets = null;

    function dressGoods() {
        Object.entries(zones).forEach(([key, zone]) => {
            const name = GOODS_MODEL[key];
            if (!name || zone.userData.dressed) return;
            let g = assets.goods(name, 0.13);
            if (g.height > 0.16) g = assets.goods(name, 0.13 * (0.16 / g.height));
            const old = zone.userData.goods;
            // Material disalin supaya sorotan rak ini tidak menular ke rak dan peti lain.
            const goods = new THREE.InstancedMesh(g.geometry, g.material.clone(), 40);
            goods.count = 0;
            goods.frustumCulled = false;
            goods.castShadow = true;
            zone.remove(old);
            zone.add(goods);
            Object.assign(zone.userData, { goods, goodsBase: 0.03, goodsHeight: g.height + 0.01, dressed: true });
            setShelf(key, zone.userData.qty ?? 0);

            const crate = crates[key];
            if (crate) {
                const sample = assets.prop(name, 0.16);
                sample.position.set(0, 0.2, 0);
                sample.traverse((o) => { if (o.isMesh) o.material = o.material.clone(); });
                crate.children[1].visible = false;
                crate.add(sample);
            }
        });
    }

    /** Memasang aset luar. Dipanggil sekali setelah loadAssets berhasil. */
    function dress(loaded) {
        assets = loaded;
        scene.environment = assets.env;
        scene.environmentIntensity = 0.55;
        scene.background = assets.sky.day;
        scene.backgroundBlurriness = 0;
        scene.fog = new THREE.Fog(new THREE.Color(0.78, 0.84, 0.86), 35, 110);
        // Langit foto sudah memberi cahaya lingkungan, jadi lampu belahan dibuat netral supaya warna model tidak kebiruan.
        hemi.color.setRGB(1, 1, 1);
        hemi.groundColor.setRGB(0.35, 0.45, 0.25);
        ground.material.color.setRGB(0.2, 0.36, 0.12);
        // Tone mapping membuat warna dasar tampak pucat, jadi lantai dan dinding digelapkan sedikit.
        // Tekstur diulang per 2 m supaya serat kayu dan anyaman bambu tidak melar di bidang yang panjang.
        const tile = (mesh, source, width, height, tint) => {
            const map = source.clone();
            map.repeat.set(width / 2, height / 2);
            mesh.material.map = map;
            mesh.material.color.setRGB(tint, tint, tint);
            mesh.material.needsUpdate = true;
        };
        tile(floor, assets.texture.floor, 10.4, 7.6, 0.8);
        solids.forEach((m) => {
            if (!m.material.color.equals(p.wall)) return;
            const { width, height, depth } = m.geometry.parameters;
            tile(m, assets.texture.wall, Math.max(width, depth), height, 0.9);
        });
        hills.material.color.setRGB(0.3, 0.46, 0.22);
        trunks.visible = false;
        crowns.visible = false;

        const put = (name, height, x, z, { y = 0, rotate = 0, collide = 0, parent = scene } = {}) => {
            const o = assets.prop(name, height);
            o.position.set(x, y, z);
            o.rotation.y = rotate;
            parent.add(o);
            if (collide) colliders.push({ minX: x - collide, maxX: x + collide, minZ: z - collide, maxZ: z + collide });
            return o;
        };

        // Bukit karst tetap dari geometri dasar. Pepohonan dari empat jenis pohon.
        // Palet daun Kenney cenderung toska. Warnanya digeser ke hijau daun supaya cocok dengan pedesaan.
        const leafy = (o) => {
            o.traverse((m) => {
                if (!m.isMesh) return;
                m.material = m.material.clone();
                const hsl = m.material.color.getHSL({});
                if (hsl.h > 0.35 && hsl.h < 0.55) m.material.color.setHSL(0.27, Math.min(0.55, hsl.s), hsl.l * 0.85);
            });
            return o;
        };
        const trees = ['tree_oak', 'tree_default', 'tree_palmTall', 'tree_detailed'];
        treeSpots.forEach(([x, z], i) => leafy(put(trees[i % trees.length], 4 + (i % 3) * 0.9, x, z, { rotate: i })));
        const scatter = [[-7, 5.4], [7.5, 4.8], [-6.5, -5.5], [6.8, -5.8], [-11, 0], [11, -1], [-3, 12.5], [3.5, 13], [-9.5, 12], [10, 12.5], [-13, 7], [13.5, 7]];
        scatter.forEach(([x, z], i) => leafy(put(['plant_bushLarge', 'plant_bush', 'grass_large', 'flower_yellowA', 'flower_redA'][i % 5], i % 5 < 2 ? 0.9 : 0.35, x, z, { rotate: i * 0.7 })));

        // Perabot di dalam dan depan warung. Benda besar ikut menjadi penghalang.
        put('kitchenFridgeSmall', 1.4, -4.55, -1.8, { rotate: Math.PI / 2, collide: 0.4 });
        put('cardboardBoxClosed', 0.45, 4.55, -1.5, { rotate: 0.2, collide: 0.3 });
        put('cardboardBoxOpen', 0.4, 4.6, -2.3, { rotate: -0.3, collide: 0.3 });
        put('cardboardBoxClosed', 0.4, 4.55, -1.5, { y: 0.45, rotate: 0.6 });
        put('pottedPlant', 0.9, 4.6, -3.6, { collide: 0.3 });
        put('trashcan', 0.6, -4.6, 1.9, { collide: 0.25 });
        put('radio', 0.2, -3.3, 2.95, { y: 1.08, rotate: Math.PI });
        put('lampSquareCeiling', 0.35, 0, -0.5, { y: 2.85 });
        put('lampSquareCeiling', 0.35, 0, 2.2, { y: 2.85 });
        put('bench', 0.5, -4.2, 6.1, { rotate: Math.PI });
        put('pottedPlant', 0.9, -5.6, 4.9);
        put('pottedPlant', 0.9, 5.6, 4.9);
        put('stoolBar', 0.75, 4.2, 4.3);

        setTime(currentTime);
        if (Object.keys(zones).length) dressGoods();
    }

    let currentTime = 0.25;
    function setTime(t) {
        currentTime = t;
        const dusk = Math.max(0, (t - 0.6) / 0.4);
        const sky = p.skyDay.clone().lerp(p.skyDusk, dusk);
        if (assets) {
            scene.background = dusk > 0.5 ? assets.sky.dusk : assets.sky.day;
            scene.environmentIntensity = 0.55 - dusk * 0.3;
            scene.fog.color.copy(new THREE.Color(0.78, 0.84, 0.86).lerp(p.skyDusk, dusk));
        } else {
            scene.background.copy(sky);
            scene.fog.color.copy(sky);
        }
        const a = Math.PI * (0.15 + t * 0.7);
        sun.position.set(Math.cos(a) * 18, Math.sin(a) * 16 + 3, 10);
        sun.intensity = (1.6 - dusk * 1.0) * (assets ? 0.75 : 1);
        hemi.intensity = (1.0 - dusk * 0.45) * (assets ? 0.45 : 1);
        indoor.intensity = 6 + dusk * 8;
    }
    setTime(0.25);

    // Titik jalan pembeli: muncul di ujung jalan, melihat etalase, antre di depan kasir.
    const spots = {
        spawnL: new THREE.Vector3(-22, 0, 9.2), spawnR: new THREE.Vector3(22, 0, 9.2),
        browse: (i) => new THREE.Vector3(1.2 + (i % 3) * 1.1, 0, 4.0),
        queue: (i) => new THREE.Vector3(-1.6, 0, 3.8 + i * 0.9),
    };

    let busTween = null;
    return {
        scene, colliders, solids, interactables, crates, zones, spots,
        bounds: { minX: -5.1, maxX: 5.1, minZ: -4.1, maxZ: 2.65 },
        setupItems,
        setTime,
        dress,
        sync(view) {
            setupItems(view.items);
            view.items.forEach((item) => {
                setShelf(item.key, item.display);
                const crate = crates[item.key];
                if (crate && ['WORLD', 'PLACED'].includes(crate.userData.state)) {
                    crate.userData.state = 'WORLD';
                    crate.visible = item.stock - item.display > 0;
                }
            });
            priceFace.userData.redraw(view.items.map((i) => ({ name: i.name, price: i.price })));
            const open = view.phase === 'melayani';
            signFace.userData.redraw(open);
            rival.visible = view.flags.competitor;
            rain.visible = view.flags.rain && !reduce;
            if (['belanja', 'harga', 'buka'].includes(view.phase)) setTime(0.22);
            if (view.phase === 'laporan') setTime(0.95);
        },
        setNotice(title, lines) { noticeFace.userData.redraw(title, lines); },
        driveBus() {
            bus.visible = true;
            bus.position.x = -40;
            busTween = { t: 0 };
        },
        tick(dt) {
            if (rain.visible) {
                const pos = rainGeo.attributes.position;
                for (let i = 0; i < rainCount; i++) {
                    let y = pos.getY(i) - dt * 14;
                    if (y < 0) y = 16;
                    pos.setY(i, y);
                }
                pos.needsUpdate = true;
            }
            if (busTween) {
                busTween.t = Math.min(1, busTween.t + dt / (reduce ? 0.01 : 4));
                bus.position.x = -40 + busTween.t * 36;
                if (busTween.t >= 1) busTween = null;
            }
        },
    };
}
