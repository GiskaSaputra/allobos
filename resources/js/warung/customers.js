import * as THREE from 'three';
import { canvasPlane, roundRect } from './world';

// Mesin keadaan pembeli: SPAWNING > APPROACHING > BROWSING > QUEUING > ORDERING > WAITING > PAYING > LEAVING.
// Server memegang daftar pembeli dan isi pesanannya. Di sini hanya gerak, antrean, dan kesabaran.

const SPEED = 1.6;
const PATIENCE = 60;
const SHIRTS = [[0.37, 0.77, 0.71], [0.8, 0.88, 0.28], [0.78, 0.72, 0.18], [0.85, 0.5, 0.45], [0.55, 0.6, 0.85], [0.95, 0.95, 0.9]];

export function createCustomers(world, { reduce, names, onMiss, onArrive }) {
    const list = [];
    const bodyGeo = new THREE.CylinderGeometry(0.22, 0.28, 0.95, 8);
    const headGeo = new THREE.SphereGeometry(0.19, 10, 8);
    let spawnTimer = 0;
    let pace = 7;
    let assets = null;

    function orderText(items) {
        return Object.entries(items).map(([k, q]) => `${names[k] ?? k} ${q}`).join(', ');
    }

    function makeNpc(data, index) {
        const group = new THREE.Group();
        // Tokoh beranimasi bila aset termuat, selain itu badan sederhana dari geometri.
        const person = assets ? assets.character(data.id + index) : null;
        if (person) {
            group.add(person.root);
            person.play('walk');
        } else {
            const color = new THREE.Color(...SHIRTS[(data.id + index) % SHIRTS.length]);
            const torso = new THREE.Mesh(bodyGeo, new THREE.MeshStandardMaterial({ color, flatShading: true }));
            torso.position.y = 0.6;
            torso.castShadow = true;
            const head = new THREE.Mesh(headGeo, new THREE.MeshStandardMaterial({ color: new THREE.Color(0.78, 0.58, 0.42), flatShading: true }));
            head.position.y = 1.28;
            head.castShadow = true;
            group.add(torso, head);
        }
        const bubble = canvasPlane(2.2, 0.5,(ctx, w, h, text = '', ratio = 1, mood = 'tunggu') => {
            if (!text) return;
            roundRect(ctx, 0, 0, w, h * 0.8, h * 0.25, 'rgb(244 251 200)');
            ctx.fillStyle = 'rgb(20 26 12)';
            ctx.font = `600 ${h * 0.24}px Ubuntu, sans-serif`;
            const fit = (w * 0.92) / ctx.measureText(text).width;
            if (fit < 1) ctx.font = `600 ${h * 0.24 * Math.max(0.55, fit)}px Ubuntu, sans-serif`;
            ctx.textAlign = 'center';
            ctx.fillText(text, w / 2, h * 0.38);
            roundRect(ctx, w * 0.1, h * 0.52, w * 0.8, h * 0.1, h * 0.05, 'rgb(219 226 196)');
            const fill = mood === 'senang' ? 'rgb(38 122 68)' : ratio > 0.35 ? 'rgb(82 102 14)' : 'rgb(180 52 36)';
            roundRect(ctx, w * 0.1, h * 0.52, w * 0.8 * Math.max(0, ratio), h * 0.1, h * 0.05, fill);
        });
        // Gelembung selalu menghadap kamera, jadi dipakai sebagai sprite dengan tekstur kanvas yang sama.
        const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: bubble.material.map, depthWrite: false }));
        sprite.userData.redraw = bubble.userData.redraw;
        sprite.scale.set(1.8, 0.41, 1);
        sprite.position.y = person ? 2.05 : 1.8;
        sprite.visible = false;
        group.add(sprite);
        const from = data.id % 2 ? world.spots.spawnR : world.spots.spawnL;
        group.position.copy(from);
        world.scene.add(group);
        return {
            data, group, person, state: 'SPAWNING', path: [], patience: PATIENCE, timer: 0, sayTimer: 0, bob: Math.random() * 6,
            say(text, mood) { sprite.visible = !!text; sprite.userData.redraw(text, this.patience / PATIENCE, mood); },
        };
    }

    const queue = () => list.filter((n) => ['QUEUING', 'ORDERING', 'WAITING'].includes(n.state)).sort((a, b) => a.queuedAt - b.queuedAt);

    function walkTo(npc, ...points) {
        npc.path = points.map((p) => p.clone());
    }

    function leave(npc) {
        npc.state = 'LEAVING';
        npc.say('');
        const exit = npc.data.id % 2 ? world.spots.spawnL : world.spots.spawnR;
        walkTo(npc, new THREE.Vector3(npc.group.position.x, 0, 7.5), exit);
    }

    function step(npc, dt) {
        const target = npc.path[0];
        npc.person?.play(target ? 'walk' : 'idle');
        if (!target) return true;
        const pos = npc.group.position;
        const delta = new THREE.Vector3(target.x - pos.x, 0, target.z - pos.z);
        const dist = delta.length();
        const move = reduce ? dist : SPEED * dt;
        if (dist <= move) {
            pos.set(target.x, 0, target.z);
            npc.path.shift();
            return npc.path.length === 0;
        }
        delta.multiplyScalar(move / dist);
        pos.add(delta);
        npc.group.rotation.y = Math.atan2(delta.x, delta.z);
        npc.bob += dt * 10;
        if (!reduce && !npc.person) npc.group.children[0].position.y = 0.6 + Math.abs(Math.sin(npc.bob)) * 0.04;
        return false;
    }

    return {
        /** Pembeli yang muncul setelah ini memakai tokoh dari aset. */
        useAssets(loaded) { assets = loaded; },
        /** Menyamakan dengan daftar pembeli dari server. Pembeli yang sudah selesai tidak dimunculkan lagi. */
        load(customers, { bus = false } = {}) {
            list.forEach((n) => world.scene.remove(n.group));
            list.length = 0;
            pace = bus ? 4 : 7;
            spawnTimer = 1.5;
            customers.filter((c) => c.status === 'menunggu').forEach((c, i) => list.push({ pending: true, data: c, order: i }));
        },
        clear() {
            list.forEach((n) => n.group && world.scene.remove(n.group));
            list.length = 0;
        },
        /** Pembeli paling depan yang siap dilayani di kasir. */
        front() {
            const first = queue()[0];
            return first && ['ORDERING', 'WAITING'].includes(first.state) ? first : null;
        },
        remaining: () => list.filter((n) => n.pending || !['PAYING', 'LEAVING', 'GONE'].includes(n.state)).length,
        paid(npc, receipt) {
            npc.state = 'PAYING';
            npc.timer = reduce ? 0.2 : 1.2;
            const mood = receipt.short > 0 ? 'kurang' : 'senang';
            npc.say(receipt.short > 0 ? `Kurang ${receipt.short}, sayang sekali` : 'Terima kasih', mood);
        },
        update(dt) {
            spawnTimer -= dt;
            const waiting = list.find((n) => n.pending);
            if (waiting && spawnTimer <= 0) {
                const npc = makeNpc(waiting.data, waiting.order);
                list.splice(list.indexOf(waiting), 1, npc);
                npc.state = 'APPROACHING';
                walkTo(npc, new THREE.Vector3(npc.group.position.x * 0.4, 0, 7), world.spots.browse(npc.data.id));
                spawnTimer = pace + Math.random() * 2;
            }

            const q = queue();
            list.forEach((npc) => {
                if (npc.pending) return;
                npc.person?.mixer.update(dt);
                const arrived = step(npc, dt);
                if (['QUEUING', 'ORDERING', 'WAITING'].includes(npc.state)) {
                    // Hanya pembeli paling depan yang menampilkan pesanan, supaya gelembung tidak bertumpuk.
                    npc.patience -= dt;
                    npc.sayTimer -= dt;
                    if (npc.sayTimer <= 0) {
                        npc.say(q[0] === npc ? orderText(npc.data.items) : '', 'pesan');
                        npc.sayTimer = 0.5;
                    }
                    if (npc.patience <= 0) {
                        onMiss(npc.data.id);
                        npc.say('');
                        leave(npc);
                        return;
                    }
                }
                switch (npc.state) {
                    case 'APPROACHING':
                        if (arrived) { npc.state = 'BROWSING'; npc.timer = reduce ? 0.2 : 2.5; npc.group.rotation.y = Math.PI; }
                        break;
                    case 'BROWSING':
                        npc.timer -= dt;
                        if (npc.timer <= 0) { npc.state = 'QUEUING'; npc.queuedAt = performance.now(); }
                        break;
                    case 'QUEUING': {
                        const slot = q.indexOf(npc);
                        const spot = world.spots.queue(Math.max(0, slot));
                        if (!npc.path.length && npc.group.position.distanceTo(spot) > 0.05) walkTo(npc, spot);
                        if (slot === 0 && arrived && npc.group.position.distanceTo(spot) < 0.06) {
                            npc.state = 'ORDERING';
                            npc.group.rotation.y = Math.PI;
                            onArrive?.(npc);
                        }
                        break;
                    }
                    case 'ORDERING':
                        npc.state = 'WAITING';
                        break;
                    case 'PAYING':
                        npc.timer -= dt;
                        if (npc.timer <= 0) leave(npc);
                        break;
                    case 'LEAVING':
                        if (arrived) { npc.state = 'GONE'; world.scene.remove(npc.group); }
                        break;
                    default:
                }
            });
        },
    };
}
