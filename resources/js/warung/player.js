import * as THREE from 'three';

// Pengendali orang pertama. Yaw di objek tubuh, pitch di kamera, jadi arah jalan tidak ikut menunduk.
// Tabrakan memakai kotak sejajar sumbu di bidang lantai. Warung hanya berisi dinding dan perabot persegi,
// jadi pustaka fisika seperti Rapier tidak sepadan dengan ukuran unduhan dan waktu muatnya.
const EYE = 1.65;
const RADIUS = 0.3;
const WALK = 2.6;
const RUN = 4.4;
const PITCH_LIMIT = Math.PI / 2 - 0.08;

export function createPlayer(camera, { colliders, bounds, reduce }) {
    const body = new THREE.Object3D();
    body.add(camera);
    camera.position.set(0, EYE, 0);
    camera.rotation.set(0, 0, 0);
    let pitch = 0;
    let bob = 0;

    function blocked(x, z) {
        if (x < bounds.minX + RADIUS || x > bounds.maxX - RADIUS || z < bounds.minZ + RADIUS || z > bounds.maxZ - RADIUS) return true;
        return colliders.some((c) => x > c.minX - RADIUS && x < c.maxX + RADIUS && z > c.minZ - RADIUS && z < c.maxZ + RADIUS);
    }

    return {
        body,
        update(dt, input, { lookLocked = false } = {}) {
            if (!lookLocked) {
                body.rotation.y -= input.look.x;
                pitch = THREE.MathUtils.clamp(pitch - input.look.y, -PITCH_LIMIT, PITCH_LIMIT);
                camera.rotation.x = pitch;
            }
            const speed = input.held('SPRINT') ? RUN : WALK;
            const forward = new THREE.Vector3(-Math.sin(body.rotation.y), 0, -Math.cos(body.rotation.y));
            const right = new THREE.Vector3(-forward.z, 0, forward.x);
            const step = forward.multiplyScalar(input.move.y).add(right.multiplyScalar(input.move.x)).multiplyScalar(speed * dt);
            // Gerak per sumbu supaya pemain meluncur di sepanjang dinding, tidak tersangkut.
            if (!blocked(body.position.x + step.x, body.position.z)) body.position.x += step.x;
            if (!blocked(body.position.x, body.position.z + step.z)) body.position.z += step.z;

            const moving = Math.hypot(input.move.x, input.move.y) > 0.1;
            bob = moving && !reduce ? bob + dt * speed * 3.2 : 0;
            camera.position.y = EYE + Math.sin(bob) * 0.035;
        },
        pose: () => ({ x: body.position.x, z: body.position.z, yaw: body.rotation.y, pitch }),
        setPose({ x, z, yaw, pitch: p }) {
            if (!blocked(x, z)) body.position.set(x, 0, z);
            body.rotation.y = yaw;
            pitch = THREE.MathUtils.clamp(p, -PITCH_LIMIT, PITCH_LIMIT);
            camera.rotation.x = pitch;
        },
    };
}
