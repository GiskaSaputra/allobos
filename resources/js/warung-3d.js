import { postJson } from './forms';

const rp = (n) => `Rp${Math.round(n).toLocaleString('id-ID')}`;

function el(tag, attrs = {}, ...children) {
    const node = document.createElement(tag);
    Object.entries(attrs).forEach(([k, v]) => {
        if (k === 'class') node.className = v;
        else if (k.startsWith('on')) node.addEventListener(k.slice(2), v);
        else if (v !== false && v !== null && v !== undefined) node.setAttribute(k, v === true ? '' : v);
    });
    children.flat().forEach((c) => node.append(c instanceof Node ? c : document.createTextNode(String(c))));
    return node;
}

function hasWebGL() {
    try {
        const c = document.createElement('canvas');
        return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
    } catch (e) {
        return false;
    }
}

function store(key, value) {
    try {
        if (value === undefined) return localStorage.getItem(key);
        localStorage.setItem(key, value);
    } catch (e) {
        return null;
    }
    return value;
}

async function initWarung(root) {
    const cfg = JSON.parse(root.dataset.config);
    const $ = (s) => root.querySelector(s);
    const body = document.body;
    const els = {
        panel: $('[data-panel]'), error: $('[data-error]'), bos: $('[data-bos]'), bosLine: $('[data-bos-line]'),
        day: $('[data-hud-day]'), theme: $('[data-hud-theme]'), cash: $('[data-hud-cash]'), sat: $('[data-hud-sat]'),
        canvas: $('[data-canvas]'), lite: $('[data-lite]'), liteCaption: $('[data-lite-caption]'), desc: $('[data-scene-desc]'),
        camera: $('[data-camera]'),
    };
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const liteBtn = body.querySelector('[data-lite-toggle]');
    const soundBtn = body.querySelector('[data-sound-toggle]');

    let view = cfg.view;
    let scene = null;
    let busy = false;
    let sound = store('warung-suara') === '1';
    let lite = store('warung-ringan') === '1' || !hasWebGL();
    const draft = { qty: {}, prices: {}, promo: 'tanpa', special: null };

    [cfg.gifIdle, cfg.gifTalk].forEach((src) => { new Image().src = src; });

    function talk(text) {
        els.bosLine.textContent = text;
        if (reduce) {
            els.bos.src = cfg.still;
            return;
        }
        els.bos.src = cfg.gifTalk;
        clearTimeout(talk.timer);
        talk.timer = setTimeout(() => { els.bos.src = cfg.gifIdle; }, Math.min(5000, 900 + text.length * 35));
    }

    function beep(freq = 880) {
        if (!sound) return;
        try {
            const ctx = beep.ctx || (beep.ctx = new AudioContext());
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.06, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.18);
            osc.connect(gain).connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.2);
        } catch (e) {
            sound = false;
        }
    }

    function applySoundButton() {
        soundBtn.setAttribute('aria-pressed', String(sound));
        soundBtn.setAttribute('aria-label', sound ? 'Matikan efek suara' : 'Nyalakan efek suara');
        soundBtn.querySelector('[data-sound-on]').classList.toggle('hidden', !sound);
        soundBtn.querySelector('[data-sound-off]').classList.toggle('hidden', sound);
    }

    async function applyLite() {
        liteBtn.textContent = lite ? 'Mode 3D' : 'Mode Ringan';
        liteBtn.disabled = !hasWebGL();
        els.lite.classList.toggle('hidden', !lite);
        els.lite.classList.toggle('flex', lite);
        els.canvas.classList.toggle('hidden', lite);
        els.camera.classList.toggle('hidden', lite);
        if (lite) {
            scene?.pause();
            return;
        }
        if (!scene) {
            try {
                const mod = await import('./warung-scene.js');
                scene = await mod.createScene(els.canvas, { reduce });
            } catch (e) {
                lite = true;
                applyLite();
                showError('Tampilan 3D tidak bisa dimuat di perangkat ini, jadi Mode Ringan dipakai.');
                return;
            }
        }
        scene.resume();
        scene.sync(view);
    }

    function showError(message) {
        els.error.textContent = message;
        els.error.classList.toggle('hidden', !message);
    }

    function hud() {
        els.day.textContent = `Hari ${view.day} dari 7`;
        els.theme.textContent = view.theme.title;
        els.cash.textContent = rp(view.cash);
        els.sat.textContent = String(view.satisfaction);
        els.liteCaption.textContent = `${view.kind_label}, hari ${view.day}: ${view.theme.title}`;
    }

    async function call(url, payload) {
        if (busy) return null;
        busy = true;
        showError('');
        try {
            return await postJson(url, payload);
        } catch (e) {
            showError(e.message);
            return null;
        } finally {
            busy = false;
        }
    }

    function stepper(label, value, { onMinus, onPlus, minusDisabled, plusDisabled, step }) {
        return el('div', { class: 'flex items-center gap-1.5' },
            el('button', { type: 'button', class: 'btn btn-secondary btn-icon btn-sm', 'aria-label': `Kurangi ${label}`, disabled: minusDisabled, onclick: onMinus }, '-'),
            el('span', { class: 'min-w-[64px] text-center font-display font-semibold text-fg', 'aria-live': 'polite' }, value),
            el('button', { type: 'button', class: 'btn btn-secondary btn-icon btn-sm', 'aria-label': `Tambah ${label}${step ? ` ${step}` : ''}`, disabled: plusDisabled, onclick: onPlus }, '+'),
        );
    }

    function eventCard() {
        if (!view.event) return '';
        return el('div', { class: 'mb-4 rounded-[20px] border border-line bg-surface-alt p-4' },
            el('p', { class: 'text-xs font-medium uppercase tracking-[0.12em] text-fg-muted' }, 'Kejadian hari ini'),
            el('p', { class: 'mt-1 font-display font-semibold text-fg' }, view.event.title),
            el('p', { class: 'mt-1 text-sm text-fg-body' }, view.event.story),
        );
    }

    function renderBuy() {
        const total = () => view.items.reduce((s, i) => s + (draft.qty[i.key] || 0) * i.cost, 0);
        const wrap = el('div', { class: 'space-y-3' });
        const draw = () => {
            wrap.replaceChildren(
                el('h2', { class: 'text-xl' }, 'Belanja stok'),
                el('p', { class: 'text-sm text-fg-muted' }, `Sisa stok kemarin ikut terjual hari ini. Biaya tetap harian dipotong saat warung tutup.`),
                ...view.items.map((item) => {
                    const qty = draft.qty[item.key] || 0;
                    return el('div', { class: 'flex items-center justify-between gap-3 rounded-[20px] border border-line p-3' },
                        el('div', { class: 'min-w-0' },
                            el('p', { class: 'font-medium text-fg' }, item.name, item.perishable ? el('span', { class: 'badge badge-warning ml-2' }, 'cepat basi') : ''),
                            el('p', { class: 'text-xs text-fg-muted' }, `${rp(item.cost)} per ${item.unit}. Stok ${item.stock}`),
                        ),
                        stepper(item.name, qty, {
                            minusDisabled: qty === 0,
                            plusDisabled: total() + item.cost > view.cash,
                            step: 'lima',
                            onMinus: () => { draft.qty[item.key] = Math.max(0, qty - 5); draw(); },
                            onPlus: () => { draft.qty[item.key] = qty + 5; draw(); },
                        }),
                    );
                }),
                el('div', { class: 'flex items-center justify-between text-sm' }, el('span', { class: 'text-fg-muted' }, 'Total belanja'), el('span', { class: 'font-display text-lg font-semibold text-fg' }, `${rp(total())} dari kas ${rp(view.cash)}`)),
                el('button', { type: 'button', class: 'btn btn-primary btn-full', onclick: submitBuy }, 'Selesai Belanja'),
            );
        };
        draw();
        return wrap;
    }

    function renderPrice() {
        view.items.forEach((i) => { draft.prices[i.key] ??= i.price; });
        const wrap = el('div', { class: 'space-y-3' });
        const draw = () => {
            wrap.replaceChildren(
                el('h2', { class: 'text-xl' }, 'Atur harga'),
                el('p', { class: 'text-sm text-fg-muted' }, 'Makin jauh di atas harga wajar, makin sedikit yang membeli. Di bawah biaya beli berarti rugi.'),
                ...view.items.map((item) => {
                    const price = draft.prices[item.key];
                    const ratio = price / item.fair;
                    return el('div', { class: 'rounded-[20px] border border-line p-3' },
                        el('div', { class: 'flex items-center justify-between gap-3' },
                            el('div', {}, el('p', { class: 'font-medium text-fg' }, item.name), el('p', { class: 'text-xs text-fg-muted' }, `Biaya ${rp(item.cost)}, harga wajar ${rp(item.fair)}`)),
                            stepper(`harga ${item.name}`, rp(price), {
                                minusDisabled: price - 500 < item.min_price,
                                plusDisabled: price + 500 > item.max_price,
                                step: 'Rp500',
                                onMinus: () => { draft.prices[item.key] = price - 500; draw(); },
                                onPlus: () => { draft.prices[item.key] = price + 500; draw(); },
                            }),
                        ),
                        el('p', { class: `mt-1 text-xs ${price < item.cost ? 'text-danger' : ratio > 1.15 ? 'text-warning' : 'text-success'}` },
                            price < item.cost ? 'Di bawah biaya, rugi per barang' : ratio > 1.15 ? 'Di atas harga wajar' : 'Dekat harga wajar'),
                    );
                }),
                el('button', { type: 'button', class: 'btn btn-primary btn-full', onclick: submitPrice }, 'Simpan Harga'),
            );
        };
        draw();
        return wrap;
    }

    function renderOpen() {
        draft.special = null;
        const wrap = el('div', { class: 'space-y-4' });
        const draw = () => {
            wrap.replaceChildren(
                eventCard(),
                el('fieldset', { class: 'space-y-2' },
                    el('legend', { class: 'mb-1 font-display text-lg font-semibold text-fg' }, 'Pilih promosi'),
                    el('div', { class: 'segment w-full', role: 'radiogroup' }, ...view.promos.map((p) => el('button', {
                        type: 'button', role: 'radio', class: 'segment-item flex-1', 'aria-checked': String(draft.promo === p.key),
                        onclick: () => { draft.promo = p.key; draw(); },
                    }, p.label))),
                    el('p', { class: 'text-xs text-fg-muted' }, (() => { const p = view.promos.find((x) => x.key === draft.promo); return `${p.cost ? `${rp(p.cost)}. ` : ''}${p.hint}`; })()),
                ),
                el('fieldset', { class: 'space-y-2' },
                    el('legend', { class: 'mb-1 font-display text-lg font-semibold text-fg' }, view.special.question),
                    ...view.special.options.map((o) => el('button', {
                        type: 'button', role: 'radio', 'aria-checked': String(draft.special === o.key),
                        class: `block w-full rounded-[20px] border p-3 text-left transition ${draft.special === o.key ? 'border-brand bg-brand/10' : 'border-line hover:border-brand/50'}`,
                        onclick: () => { draft.special = o.key; draw(); },
                    }, el('span', { class: 'block font-medium text-fg' }, o.label), el('span', { class: 'block text-xs text-fg-muted' }, o.hint))),
                ),
                el('button', { type: 'button', class: 'btn btn-primary btn-lg btn-full', disabled: !draft.special, onclick: submitRun }, 'Buka Warung Hari Ini'),
                draft.special ? '' : el('p', { class: 'text-center text-xs text-fg-muted' }, 'Pilih satu keputusan dulu.'),
            );
        };
        draw();
        return wrap;
    }

    function renderReport() {
        const r = view.result;
        return el('div', { class: 'space-y-4' },
            el('h2', { class: 'text-xl' }, `Laporan hari ${view.day}`),
            el('div', { class: 'grid grid-cols-2 gap-2' },
                ...[['Omzet', rp(r.revenue)], ['Laba', rp(r.profit)], ['Kas', rp(r.cash)], ['Kepuasan', `${r.satisfaction_before} ke ${r.satisfaction_after}`]].map(([k, v]) => el('div', { class: 'rounded-[20px] bg-surface-alt p-3' }, el('p', { class: 'text-xs text-fg-muted' }, k), el('p', { class: `font-display text-lg font-semibold ${k === 'Laba' && r.profit < 0 ? 'text-danger' : 'text-fg'}` }, v))),
            ),
            r.hit_target ? el('p', { class: 'rounded-[20px] bg-success/15 p-3 text-sm text-success' }, `Target harian ${rp(view.target)} tercapai.`) : '',
            el('ul', { class: 'grid gap-2' }, ...r.items.map((i) => el('li', { class: 'rounded-[20px] border border-line p-3 text-sm' },
                el('p', { class: 'font-medium text-fg' }, i.name),
                el('p', { class: 'text-fg-body' }, `Terjual ${i.sold}, tidak kebagian ${i.lost}, sisa ${i.left}, terbuang ${i.wasted}`),
            ))),
            el('p', { class: 'text-xs text-fg-muted' }, `Biaya: HPP terjual ${rp(r.cogs)}, promosi ${rp(r.promo_cost)}, keputusan ${rp(r.special_cost)}, biaya tetap ${rp(r.fixed_cost)}${r.event_cash ? `, kejadian ${rp(r.event_cash)}` : ''}. Barang terbuang ${rp(r.waste_value)}.`),
            el('button', { type: 'button', class: 'btn btn-primary btn-full', onclick: submitNext }, view.day >= 7 ? 'Lihat Rapor' : 'Lanjut ke Hari Berikutnya'),
        );
    }

    function render() {
        hud();
        const panel = { belanja: renderBuy, harga: renderPrice, buka: renderOpen, laporan: renderReport }[view.phase];
        els.panel.replaceChildren(panel ? panel() : el('p', {}, 'Memuat'));
        if (view.phase === 'laporan') talk(view.result.bos_comment);
        else if (view.phase === 'belanja') talk(view.bos_line);
        els.desc.textContent = `Hari ${view.day}, ${view.theme.title}. Kas ${rp(view.cash)}.`;
        if (scene && !lite) scene.sync(view);
    }

    async function submitBuy() {
        const qty = Object.fromEntries(view.items.map((i) => [i.key, draft.qty[i.key] || 0]));
        const data = await call(cfg.urls.buy, { qty });
        if (!data) return;
        view = data.view;
        draft.qty = {};
        beep(660);
        render();
    }

    async function submitPrice() {
        const prices = Object.fromEntries(view.items.map((i) => [i.key, draft.prices[i.key] ?? i.price]));
        const data = await call(cfg.urls.price, { prices });
        if (!data) return;
        view = data.view;
        render();
    }

    async function submitRun() {
        const button = els.panel.querySelector('.btn-lg');
        if (button) {
            button.disabled = true;
            button.textContent = 'Warung sedang buka';
        }
        const data = await call(cfg.urls.run, { promo: draft.promo, special: draft.special });
        if (!data) {
            if (button) { button.disabled = false; button.textContent = 'Buka Warung Hari Ini'; }
            return;
        }
        talk('Warung buka. Mari lihat siapa yang datang.');
        if (scene && !lite) {
            await scene.playDay(view, data.result, { onSale: () => beep(1046) });
        }
        view = data.view;
        draft.prices = {};
        render();
    }

    async function submitNext() {
        const data = await call(cfg.urls.next, {});
        if (!data) return;
        if (data.finished) {
            window.location.href = data.redirect;
            return;
        }
        view = data.view;
        draft.promo = 'tanpa';
        render();
    }

    liteBtn.addEventListener('click', () => {
        lite = !lite;
        store('warung-ringan', lite ? '1' : '0');
        applyLite();
    });
    soundBtn.addEventListener('click', () => {
        sound = !sound;
        store('warung-suara', sound ? '1' : '0');
        applySoundButton();
        beep(880);
    });
    els.camera.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-cam]');
        if (btn && scene) scene.camera(btn.dataset.cam);
    });

    applySoundButton();
    render();
    await applyLite();
}

const root = document.querySelector('[data-warung]');
if (root) initWarung(root);
