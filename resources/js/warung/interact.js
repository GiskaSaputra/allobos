import * as THREE from 'three';

// Sinar dari tengah layar, paling jauh 3 meter. Benda pertama yang kena menentukan target,
// jadi dinding atau meja di depan benda otomatis menghalangi (oklusi).
// Keadaan benda: WORLD (di tempatnya), HELD (di tangan), INSPECTING (diamati), PLACED (masuk rak), USING (stasiun dipakai).

const MAX_DISTANCE = 3;
const HIGHLIGHT = new THREE.Color(0.32, 0.36, 0.08);

export function createInteraction(camera, world) {
    const ray = new THREE.Raycaster();
    ray.far = MAX_DISTANCE;
    const center = new THREE.Vector2(0, 0);
    const targets = () => [...world.solids, ...world.interactables];
    let current = null;
    let held = null;

    // Soket tangan menempel di kamera. Benda dipindah dengan attach() supaya posisi dunia tidak meloncat.
    const socket = new THREE.Object3D();
    socket.position.set(0.38, -0.32, -0.75);
    camera.add(socket);
    const inspectPos = new THREE.Vector3(0, -0.05, -0.55);
    const holdPos = socket.position.clone();

    function resolve(object) {
        let o = object;
        while (o && !o.userData?.interactive) o = o.parent;
        return o && o.visible ? o : null;
    }

    function highlight(object, on) {
        object?.traverse((o) => {
            if (!o.isMesh || !o.material.emissive) return;
            o.material.emissive.copy(on ? HIGHLIGHT : new THREE.Color(0, 0, 0));
        });
    }

    return {
        get target() { return current; },
        get held() { return held; },
        /** Memperbarui target yang dilihat. Tidak memicu aksi apa pun, aksi hanya lewat masukan eksplisit. */
        update() {
            ray.setFromCamera(center, camera);
            const hit = ray.intersectObjects(targets(), true).find((h) => !held || !isChild(h.object, held));
            const next = hit ? resolve(hit.object) : null;
            if (next !== current) {
                highlight(current, false);
                current = next;
                highlight(current, true);
            }
            return current;
        },
        pickUp(object) {
            highlight(object, false);
            object.userData.state = 'HELD';
            socket.attach(object);
            object.position.set(0, 0, 0);
            object.rotation.set(0.15, -0.35, 0);
            object.scale.setScalar(0.7);
            held = object;
            current = null;
        },
        /** Mengembalikan benda ke tempat asalnya (rak gudang). */
        drop() {
            if (!held) return null;
            const object = held;
            const home = object.userData.homeParent;
            home.attach(object);
            object.position.copy(object.userData.home);
            object.rotation.set(0, 0, 0);
            object.scale.setScalar(1);
            object.userData.state = 'WORLD';
            socket.position.copy(holdPos);
            held = null;
            return object;
        },
        /** Benda masuk rak pajang: disembunyikan, lalu dikembalikan ke gudang tanpa terlihat. */
        place() {
            const object = this.drop();
            if (!object) return null;
            object.userData.state = 'PLACED';
            object.visible = false;
            return object;
        },
        inspect(on, look) {
            if (!held) return false;
            held.userData.state = on ? 'INSPECTING' : 'HELD';
            socket.position.lerp(on ? inspectPos : holdPos, 0.25);
            if (on && look) {
                held.rotation.y += look.x * 2;
                held.rotation.x += look.y * 2;
            }
            return on;
        },
    };
}

function isChild(object, parent) {
    for (let o = object; o; o = o.parent) if (o === parent) return true;
    return false;
}
