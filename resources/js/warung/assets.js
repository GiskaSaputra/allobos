import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { HDRLoader } from 'three/addons/loaders/HDRLoader.js';
import { clone as cloneSkinned } from 'three/addons/utils/SkeletonUtils.js';

// Aset CC0: Kenney (makanan, alam, perabot), Quaternius (tokoh), Poly Haven (langit). Rincian di public/models/warung/LISENSI.txt.
const BASE = '/models/warung';
const PROPS = {
    makanan: ['bag-flat', 'rice-ball', 'pudding', 'cup-tea', 'soda-bottle', 'chinese', 'loaf', 'egg', 'honey', 'cookie', 'bag', 'carton-small'],
    alam: ['tree_oak', 'tree_default', 'tree_palmTall', 'tree_detailed', 'plant_bushLarge', 'plant_bush', 'grass_large', 'flower_yellowA', 'flower_redA'],
    perabot: ['kitchenFridgeSmall', 'cardboardBoxClosed', 'cardboardBoxOpen', 'pottedPlant', 'plantSmall2', 'stoolBar', 'bench', 'radio', 'lampSquareCeiling', 'trashcan', 'bookcaseOpen'],
};
const CHARACTERS = ['woman', 'character'];

/** Ukuran terbesar sebuah objek dalam meter. */
function extent(object) {
    const size = new THREE.Box3().setFromObject(object).getSize(new THREE.Vector3());
    return { size, max: Math.max(size.x, size.y, size.z) };
}

/** Tinggi tokoh berkerangka. Ukuran geometrinya baru benar setelah tulang diterapkan, jadi Box3 biasa keliru. */
const heights = new WeakMap();
function heightOf(gltf) {
    if (heights.has(gltf)) return heights.get(gltf);
    const box = new THREE.Box3();
    gltf.scene.updateMatrixWorld(true);
    gltf.scene.traverse((o) => {
        if (!o.isSkinnedMesh) return;
        o.skeleton.update();
        o.computeBoundingBox();
        box.union(o.boundingBox.clone().applyMatrix4(o.matrixWorld));
    });
    const height = box.isEmpty() ? 0 : box.getSize(new THREE.Vector3()).y;
    heights.set(gltf, height);
    return height;
}

function shadowed(object) {
    object.traverse((o) => {
        if (o.isMesh) { o.castShadow = true; o.receiveShadow = true; }
    });
    return object;
}

/**
 * Memuat semua aset sekaligus. Gagal satu berarti gagal semua, supaya dunia tidak setengah berganti.
 * Hasil: prop(nama, tinggi) membuat salinan bertinggi tertentu, goods(nama, lebar) memberi geometri dan material
 * untuk InstancedMesh, character(i) membuat tokoh beranimasi, sky dan env untuk latar.
 */
export async function loadAssets(renderer) {
    const gltf = new GLTFLoader();
    const load = (url) => gltf.loadAsync(url);
    const texture = (url) => new THREE.TextureLoader().loadAsync(url).then((t) => {
        t.mapping = THREE.EquirectangularReflectionMapping;
        t.colorSpace = THREE.SRGBColorSpace;
        return t;
    });

    const entries = Object.entries(PROPS).flatMap(([dir, names]) => names.map((name) => [name, `${BASE}/${dir}/${name}.glb`]));
    const [models, people, day, dusk, hdr] = await Promise.all([
        Promise.all(entries.map(([name, url]) => load(url).then((g) => [name, g.scene]))),
        Promise.all(CHARACTERS.map((name) => load(`${BASE}/tokoh/${name}.glb`))),
        texture(`${BASE}/langit/day.jpg`),
        texture(`${BASE}/langit/dusk.jpg`),
        new HDRLoader().loadAsync(`${BASE}/langit/day_1k.hdr`),
    ]);

    const pmrem = new THREE.PMREMGenerator(renderer);
    const env = pmrem.fromEquirectangular(hdr).texture;
    hdr.dispose();
    pmrem.dispose();

    const scenes = Object.fromEntries(models);

    return {
        sky: { day, dusk },
        env,
        has: (name) => name in scenes,
        prop(name, height) {
            const copy = shadowed(scenes[name].clone());
            const { size } = extent(copy);
            copy.scale.multiplyScalar(height / (size.y || 1));
            return copy;
        },
        /** Satu geometri berpusat di alas, lebarnya diskalakan, untuk dipakai berulang di rak. */
        goods(name, width) {
            let mesh = null;
            scenes[name].updateMatrixWorld(true);
            scenes[name].traverse((o) => { if (o.isMesh && !mesh) mesh = o; });
            const geometry = mesh.geometry.clone().applyMatrix4(mesh.matrixWorld);
            geometry.computeBoundingBox();
            const box = geometry.boundingBox;
            const size = box.getSize(new THREE.Vector3());
            geometry.translate(-(box.min.x + size.x / 2), -box.min.y, -(box.min.z + size.z / 2));
            const s = width / Math.max(size.x, size.z);
            geometry.scale(s, s, s);
            return { geometry, material: mesh.material, height: size.y * s };
        },
        /** Tokoh Quaternius bertinggi 1,65 m dengan klip jalan dan diam. */
        character(index) {
            const source = people[index % people.length];
            const root = cloneSkinned(source.scene);
            shadowed(root);
            root.traverse((o) => { if (o.isMesh) o.frustumCulled = false; });
            root.scale.multiplyScalar(1.65 / (heightOf(source) || 1));
            const mixer = new THREE.AnimationMixer(root);
            const clip = (pattern) => source.animations.find((a) => pattern.test(a.name.split('|').pop()));
            const actions = {
                walk: mixer.clipAction(clip(/^Walk(ing)?$/i) ?? source.animations[0]),
                idle: mixer.clipAction(clip(/^Idle$/i) ?? source.animations[0]),
            };
            let current = null;
            return {
                root,
                mixer,
                play(name) {
                    const next = actions[name];
                    if (next === current) return;
                    next.reset().fadeIn(0.2).play();
                    current?.fadeOut(0.2);
                    current = next;
                },
            };
        },
    };
}
