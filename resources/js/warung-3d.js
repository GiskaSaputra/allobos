import { postJson } from './forms';
import { playClip } from './avatar';

const rp = (n) => `Rp${Math.round(n).toLocaleString('id-ID')}`;
const rid = () => (crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random().toString(36).slice(2)}`);

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

const SHEET_LITE = 'absolute inset-x-0 bottom-0 z-20 max-h-[62%] overflow-y-auto rounded-t-[28px] border-t border-line bg-surface p-4 shadow-float lg:inset-y-0 lg:left-auto lg:right-0 lg:max-h-none lg:w-[420px] lg:rounded-none lg:border-l lg:border-t-0 lg:p-5';
const SHEET_MODAL = 'absolute left-1/2 top-1/2 z-30 max-h-[calc(100%-24px)] w-[min(460px,calc(100%-24px))] -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-[28px] border border-line bg-surface p-5 shadow-float';

async function initWarung(root) {
    const cfg = JSON.parse(root.dataset.config);
    const $ = (s) => root.querySelector(s);
    const els = {
        stage: $('[data-stage]'), sheet: $('[data-sheet]'), sheetTitle: $('[data-sheet-title]'), sheetClose: $('[data-sheet-close]'),
        panel: $('[data-panel]'), error: $('[data-error]'), bos: $('[data-bos]'), bosLine: $('[data-bos-line]'),
        day: $('[data-hud-day]'), theme: $('[data-hud-theme]'), cash: $('[data-hud-cash]'), sat: $('[data-hud-sat]'),
        canvas: $('[data-canvas]'), lite: $('[data-lite]'), liteCaption: $('[data-lite-caption]'), desc: $('[data-scene-desc]'),
        objective: $('[data-objective]'), prompt: $('[data-prompt]'), crosshair: $('[data-crosshair]'), touch: $('[data-touch]'),
        start: $('[data-start]'), controls: $('[data-controls]'),
    };
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const coarse = window.matchMedia('(pointer: coarse)').matches;
    const liteBtn = document.querySelector('[data-lite-toggle]');
    const soundBtn = document.querySelector('[data-sound-toggle]');

    let view = cfg.view;
    let game = null;
    let busy = false;
    let sound = store('warung-suara') === '1';
    let lite = store('warung-ringan') === '1' || !hasWebGL();
    let sheetKind = null;
    let device = coarse ? 'touch' : 'keyboard';
    const draft = { qty: {}, prices: {}, promo: view.decisions?.promo ?? 'tanpa', special: view.decisions?.special ?? null };

    // ---------- Bos Allo, suara, galat ----------

    function talk(text) {
        if (!text) return;
        els.bosLine.textContent = text;
        playClip(els.bos, cfg.clipTalk);
        clearTimeout(talk.timer);
        talk.timer = setTimeout(() => playClip(els.bos, cfg.clipIdle), Math.min(5000, 900 + text.length * 35));
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

    function showError(message) {
        els.error.textContent = message;
        els.error.classList.toggle('hidden', !message);
        if (message && !lite && !sheetKind) talk(message);
    }

    /** Kiriman ke server. Galat jaringan diulang sekali: rid membuat kiriman ulang aman. */
    async function call(url, payload, { quiet = false } = {}) {
        if (busy) return null;
        busy = true;
        showError('');
        try {
            try {
                return await postJson(url, payload);
            } catch (e) {
                if (e.status) throw e;
                await new Promise((r) => setTimeout(r, 800));
                return await postJson(url, payload);
            }
        } catch (e) {
            if (!quiet) showError(e.message);
            return null;
        } finally {
            busy = false;
        }
    }

    // ---------- HUD dan tujuan ----------

    const onShelf = () => view.items.reduce((s, i) => s + i.display, 0);
    const inStore = (key) => { const i = view.items.find((x) => x.key === key); return i ? i.stock - i.display : 0; };

    function objectives() {
        const list = [];
        if (view.phase === 'belanja') list.push(lite ? 'Belanja stok untuk hari ini' : 'Belanja stok di meja pemasok');
        if (view.phase === 'harga') list.push(lite ? 'Atur harga jual' : 'Atur harga di papan harga');
        if (['harga', 'buka'].includes(view.phase) && !lite && onShelf() === 0) list.push('Bawa peti dari gudang ke rak pajang');
        if (view.phase === 'buka') {
            if (!draft.special) list.push('Pilih keputusan hari ini di papan pengumuman');
            list.push(lite ? 'Buka warung hari ini' : 'Balik papan BUKA di ujung meja depan');
        }
        if (view.phase === 'melayani') {
            const left = game?.remaining() ?? 0;
            list.push(left > 0 ? `Layani pembeli di kasir, ${left} lagi` : 'Semua pembeli selesai. Balik papan TUTUP');
        }
        if (view.phase === 'laporan') list.push(view.day >= 7 ? 'Baca laporan lalu lihat rapor' : 'Baca laporan lalu lanjut ke hari berikutnya');
        return list;
    }

    function hud() {
        els.day.textContent = `Hari ${view.day} dari 7`;
        els.theme.textContent = view.theme.title;
        els.cash.textContent = rp(view.cash);
        els.sat.textContent = String(view.satisfaction);
        els.liteCaption.textContent = `${view.kind_label}, hari ${view.day}: ${view.theme.title}`;
        els.objective.textContent = objectives()[0] ?? '';
        els.objective.classList.toggle('hidden', !els.objective.textContent);
        els.desc.textContent = `Hari ${view.day}, ${view.theme.title}. Kas ${rp(view.cash)}. Tujuan: ${els.objective.textContent}`;
    }

    // ---------- Panel ----------

    function openSheet(kind, title, content) {
        sheetKind = kind;
        els.sheetTitle.textContent = title;
        els.panel.replaceChildren(content);
        els.sheet.classList.remove('hidden');
        els.sheetClose.classList.toggle('hidden', lite);
        els.sheet.setAttribute('aria-modal', String(!lite));
        if (!lite) {
            game?.setActive(false);
            els.prompt.classList.add('hidden');
            requestAnimationFrame(() => focusables()[0]?.focus({ preventScroll: true }));
        }
    }

    function closeSheet({ relock = true } = {}) {
        if (lite) return;
        sheetKind = null;
        els.sheet.classList.add('hidden');
        showError('');
        game?.setActive(true);
        if (relock) game?.lock();
    }

    const focusables = () => [...els.sheet.querySelectorAll('button:not([disabled]), a[href], input, select')].filter((n) => n.offsetParent !== null);

    /** Navigasi panel dengan gamepad: arah atas dan bawah memindah fokus, A menekan, B menutup. */
    let padCooldown = 0;
    function padUi(pad) {
        if (!sheetKind) return;
        const list = focusables();
        const index = list.indexOf(document.activeElement);
        const now = performance.now();
        const vertical = pad.axes[1] || 0;
        const horizontal = pad.axes[0] || 0;
        let dir = 0;
        if (pad.edges[12]) dir = -1;
        if (pad.edges[13]) dir = 1;
        if (pad.edges[14]) dir = -1;
        if (pad.edges[15]) dir = 1;
        if (!dir && now > padCooldown && Math.abs(vertical) + Math.abs(horizontal) > 0.6) {
            dir = (Math.abs(vertical) > Math.abs(horizontal) ? vertical : horizontal) > 0 ? 1 : -1;
            padCooldown = now + 220;
        }
        if (dir) list[(index + dir + list.length) % list.length]?.focus();
        if (pad.edges[0]) document.activeElement?.click?.();
        if (pad.edges[1] || pad.edges[9]) closeSheet({ relock: false });
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
        return el('div', { class: 'rounded-[20px] border border-line bg-surface-alt p-4' },
            el('p', { class: 'text-xs font-medium uppercase tracking-[0.12em] text-fg-muted' }, 'Kejadian hari ini'),
            el('p', { class: 'mt-1 font-display font-semibold text-fg' }, view.event.title),
            el('p', { class: 'mt-1 text-sm text-fg-body' }, view.event.story),
        );
    }

    function redraw(draw) {
        const wrap = el('div', { class: 'space-y-3' });
        const paint = () => {
            const focusedLabel = document.activeElement?.getAttribute?.('aria-label');
            wrap.replaceChildren(...draw(paint));
            if (focusedLabel) wrap.querySelector(`[aria-label="${CSS.escape(focusedLabel)}"]`)?.focus();
        };
        paint();
        return wrap;
    }

    function panelBuy() {
        return redraw((paint) => {
            const total = view.items.reduce((s, i) => s + (draft.qty[i.key] || 0) * i.cost, 0);
            return [
                el('h2', { class: 'text-xl' }, 'Belanja stok'),
                el('p', { class: 'text-sm text-fg-muted' }, lite ? 'Sisa stok kemarin ikut terjual hari ini.' : 'Barang yang dibeli masuk gudang. Bawa petinya ke rak pajang supaya bisa dibeli pembeli.'),
                ...view.items.map((item) => {
                    const qty = draft.qty[item.key] || 0;
                    return el('div', { class: 'flex items-center justify-between gap-3 rounded-[20px] border border-line p-3' },
                        el('div', { class: 'min-w-0' },
                            el('p', { class: 'font-medium text-fg' }, item.name, item.perishable ? el('span', { class: 'badge badge-warning ml-2' }, 'cepat basi') : ''),
                            el('p', { class: 'text-xs text-fg-muted' }, `${rp(item.cost)} per ${item.unit}. Stok ${item.stock}${lite ? '' : `, di rak ${item.display}`}`),
                        ),
                        stepper(item.name, qty, {
                            minusDisabled: qty === 0,
                            plusDisabled: total + item.cost * 5 > view.cash,
                            step: 'lima',
                            onMinus: () => { draft.qty[item.key] = Math.max(0, qty - 5); paint(); },
                            onPlus: () => { draft.qty[item.key] = qty + 5; paint(); },
                        }),
                    );
                }),
                el('div', { class: 'flex items-center justify-between text-sm' }, el('span', { class: 'text-fg-muted' }, 'Total belanja'), el('span', { class: 'font-display text-lg font-semibold text-fg' }, `${rp(total)} dari kas ${rp(view.cash)}`)),
                el('button', { type: 'button', class: 'btn btn-primary btn-full', disabled: total === 0 && view.phase !== 'belanja', onclick: submitBuy }, lite ? 'Selesai Belanja' : 'Bayar ke Pemasok'),
            ];
        });
    }

    function panelPrice() {
        view.items.forEach((i) => { draft.prices[i.key] ??= i.price; });
        return redraw((paint) => [
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
                            onMinus: () => { draft.prices[item.key] = price - 500; paint(); },
                            onPlus: () => { draft.prices[item.key] = price + 500; paint(); },
                        }),
                    ),
                    el('p', { class: `mt-1 text-xs ${price < item.cost ? 'text-danger' : ratio > 1.15 ? 'text-warning' : 'text-success'}` },
                        price < item.cost ? 'Di bawah biaya, rugi per barang' : ratio > 1.15 ? 'Di atas harga wajar' : 'Dekat harga wajar'),
                );
            }),
            el('button', { type: 'button', class: 'btn btn-primary btn-full', onclick: submitPrice }, 'Simpan Harga'),
        ]);
    }

    function panelDecisions({ withOpen }) {
        return redraw((paint) => {
            const promo = view.promos.find((x) => x.key === draft.promo);
            const locked = view.phase === 'melayani' || view.phase === 'laporan';
            return [
                eventCard(),
                el('fieldset', { class: 'space-y-2', disabled: locked },
                    el('legend', { class: 'mb-1 font-display text-lg font-semibold text-fg' }, 'Pilih promosi'),
                    el('div', { class: 'segment w-full', role: 'radiogroup' }, ...view.promos.map((p) => el('button', {
                        type: 'button', role: 'radio', class: 'segment-item flex-1', 'aria-checked': String(draft.promo === p.key), 'aria-label': p.label,
                        onclick: () => { draft.promo = p.key; paint(); hud(); },
                    }, p.label))),
                    el('p', { class: 'text-xs text-fg-muted' }, `${promo.cost ? `${rp(promo.cost)}. ` : ''}${promo.hint}`),
                ),
                el('fieldset', { class: 'space-y-2', disabled: locked },
                    el('legend', { class: 'mb-1 font-display text-lg font-semibold text-fg' }, view.special.question),
                    ...view.special.options.map((o) => el('button', {
                        type: 'button', role: 'radio', 'aria-checked': String(draft.special === o.key), 'aria-label': o.label,
                        class: `block w-full rounded-[20px] border p-3 text-left transition ${draft.special === o.key ? 'border-brand bg-brand/10' : 'border-line hover:border-brand/50'}`,
                        onclick: () => { draft.special = o.key; paint(); hud(); },
                    }, el('span', { class: 'block font-medium text-fg' }, o.label), el('span', { class: 'block text-xs text-fg-muted' }, o.hint))),
                ),
                withOpen
                    ? el('button', { type: 'button', class: 'btn btn-primary btn-lg btn-full', disabled: !draft.special || view.phase !== 'buka', onclick: submitRun }, 'Buka Warung Hari Ini')
                    : el('button', { type: 'button', class: 'btn btn-primary btn-full', disabled: !draft.special || locked, onclick: () => { closeSheet(); talk('Pilihan dicatat. Kalau rak sudah terisi, balik papan BUKA di meja depan.'); } }, 'Simpan Pilihan'),
                draft.special || locked ? '' : el('p', { class: 'text-center text-xs text-fg-muted' }, 'Pilih satu keputusan dulu.'),
            ];
        });
    }

    function panelOpenConfirm() {
        const promo = view.promos.find((x) => x.key === draft.promo);
        const special = view.special.options.find((o) => o.key === draft.special);
        return el('div', { class: 'space-y-3' },
            el('h2', { class: 'text-xl' }, 'Buka warung sekarang?'),
            el('ul', { class: 'grid gap-2 text-sm' },
                ...view.items.map((i) => el('li', { class: 'flex justify-between rounded-[16px] bg-surface-alt px-3 py-2' }, el('span', { class: 'text-fg-body' }, i.name), el('span', { class: 'font-medium text-fg' }, `di rak ${i.display}, ${rp(i.price)}`))),
            ),
            el('p', { class: 'text-sm text-fg-muted' }, `Promosi: ${promo.label}. Keputusan: ${special.label}. Barang yang tidak ada di rak tidak bisa dibeli.`),
            el('button', { type: 'button', class: 'btn btn-primary btn-lg btn-full', onclick: submitOpen }, 'Balik Papan ke BUKA'),
        );
    }

    function panelCloseConfirm() {
        const left = game?.remaining() ?? 0;
        return el('div', { class: 'space-y-3' },
            el('h2', { class: 'text-xl' }, 'Tutup warung?'),
            el('p', { class: 'text-sm text-fg-body' }, left > 0 ? `Masih ada ${left} pembeli. Bila ditutup sekarang, mereka pulang dan dihitung tidak kebagian.` : 'Semua pembeli sudah selesai. Buku hari ini akan ditutup.'),
            el('button', { type: 'button', class: `btn ${left > 0 ? 'btn-danger' : 'btn-primary'} btn-lg btn-full`, onclick: submitClose }, 'Tutup dan Hitung Hasil'),
        );
    }

    function panelServeAll() {
        return el('div', { class: 'space-y-3' },
            el('h2', { class: 'text-xl' }, 'Warung sedang buka'),
            el('p', { class: 'text-sm text-fg-body' }, `Hari ini dimulai di mode 3D. Ada ${view.customers.filter((c) => c.status === 'menunggu').length} pembeli menunggu. Mode Ringan melayani mereka dari barang di rak lalu menutup warung.`),
            el('button', { type: 'button', class: 'btn btn-primary btn-lg btn-full', onclick: serveAllAndClose }, 'Layani Semua lalu Tutup'),
        );
    }

    function panelStock() {
        return el('div', { class: 'space-y-3' },
            el('h2', { class: 'text-xl' }, 'Isi warung'),
            el('ul', { class: 'grid gap-2 text-sm' }, ...view.items.map((i) => el('li', { class: 'rounded-[16px] border border-line px-3 py-2' },
                el('p', { class: 'font-medium text-fg' }, i.name),
                el('p', { class: 'text-fg-muted' }, `Gudang ${i.stock - i.display}, rak ${i.display} dari ${view.shelf_capacity}, harga ${rp(i.price)}`),
            ))),
            game?.holding ? el('p', { class: 'text-sm text-fg-body' }, `Sedang membawa peti ${view.items.find((i) => i.key === game.holding)?.name}.`) : '',
            el('p', { class: 'text-xs text-fg-muted' }, `Target laba harian ${rp(view.target)}.`),
        );
    }

    function panelReport() {
        const r = view.result;
        return el('div', { class: 'space-y-4' },
            el('h2', { class: 'text-xl' }, `Laporan hari ${view.day}`),
            el('div', { class: 'grid grid-cols-2 gap-2' },
                ...[['Omzet', rp(r.revenue)], ['Laba', rp(r.profit)], ['Kas', rp(r.cash)], ['Kepuasan', `${r.satisfaction_before} ke ${r.satisfaction_after}`]].map(([k, v]) => el('div', { class: 'rounded-[20px] bg-surface-alt p-3' }, el('p', { class: 'text-xs text-fg-muted' }, k), el('p', { class: `font-display text-lg font-semibold ${k === 'Laba' && r.profit < 0 ? 'text-danger' : 'text-fg'}` }, v))),
            ),
            r.customers_total ? el('p', { class: 'text-sm text-fg-body' }, `Pembeli dilayani ${r.customers_served} dari ${r.customers_total}.`) : '',
            r.hit_target ? el('p', { class: 'rounded-[20px] bg-success/15 p-3 text-sm text-success' }, `Target harian ${rp(view.target)} tercapai.`) : '',
            el('ul', { class: 'grid gap-2' }, ...r.items.map((i) => el('li', { class: 'rounded-[20px] border border-line p-3 text-sm' },
                el('p', { class: 'font-medium text-fg' }, i.name),
                el('p', { class: 'text-fg-body' }, `Terjual ${i.sold}, tidak kebagian ${i.lost}, sisa ${i.left}, terbuang ${i.wasted}`),
            ))),
            el('p', { class: 'text-xs text-fg-muted' }, `Biaya: HPP terjual ${rp(r.cogs)}, promosi ${rp(r.promo_cost)}, keputusan ${rp(r.special_cost)}, biaya tetap ${rp(r.fixed_cost)}${r.event_cash ? `, kejadian ${rp(r.event_cash)}` : ''}. Barang terbuang ${rp(r.waste_value)}.`),
            el('button', { type: 'button', class: 'btn btn-primary btn-full', onclick: submitNext }, view.day >= 7 ? 'Lihat Rapor' : 'Lanjut ke Hari Berikutnya'),
        );
    }

    function panelPause() {
        const controls = controlList();
        return el('div', { class: 'space-y-3' },
            el('h2', { class: 'text-xl' }, 'Jeda'),
            el('button', { type: 'button', class: 'btn btn-primary btn-full', onclick: () => closeSheet() }, 'Lanjutkan'),
            el('button', { type: 'button', class: 'btn btn-secondary btn-full', onclick: () => { closeSheet({ relock: false }); toggleLite(); } }, 'Pakai Mode Ringan'),
            el('button', { type: 'button', class: 'btn btn-secondary btn-full', onclick: () => { toggleSound(); openSheet('pause', 'Menu', panelPause()); } }, sound ? 'Matikan Efek Suara' : 'Nyalakan Efek Suara'),
            el('a', { href: cfg.urls.exit, class: 'btn btn-ghost btn-full' }, 'Keluar ke Daftar Permainan'),
            el('dl', { class: 'grid grid-cols-2 gap-x-4 gap-y-1 border-t border-line pt-3 text-sm' }, ...controls),
            el('p', { class: 'text-xs text-fg-muted' }, 'Kemajuan tersimpan di server setiap kali kamu bertransaksi.'),
        );
    }

    function controlList() {
        const g = (a) => game?.input.glyph(a) ?? '';
        const rows = device === 'gamepad'
            ? [['Jalan', 'Stik kiri'], ['Lihat', 'Stik kanan'], ['Interaksi', g('INTERACT')], ['Ambil atau kembalikan', g('PICK_UP')], ['Amati barang', g('INSPECT')], ['Isi warung', g('INVENTORY')], ['Jeda', g('PAUSE')]]
            : device === 'touch'
                ? [['Jalan', 'Lingkaran kiri'], ['Lihat', 'Geser sisi kanan'], ['Interaksi', 'Tombol Pakai'], ['Ambil atau kembalikan', 'Tombol Ambil'], ['Jeda', 'Tombol jeda']]
                : [['Jalan', 'W A S D'], ['Lihat', 'Tetikus'], ['Lari', 'Shift'], ['Interaksi', 'E atau klik'], ['Ambil atau kembalikan', 'F'], ['Amati barang', 'Tahan klik kanan'], ['Isi warung', 'Tab'], ['Jeda', 'Esc']];
        return rows.flatMap(([k, v]) => [el('dt', { class: 'text-fg-muted' }, k), el('dd', { class: 'font-medium text-fg' }, v)]);
    }

    // ---------- Stasiun dunia 3D ----------

    const hooks = {
        objectives: () => objectives(),
        prompt(text) {
            els.prompt.textContent = text;
            els.prompt.classList.toggle('hidden', !text || !!sheetKind);
        },
        label({ kind }) {
            if (kind === 'sign') return view.phase === 'melayani' ? 'Balik papan ke TUTUP' : view.phase === 'laporan' ? 'Lihat laporan hari ini' : 'Balik papan ke BUKA';
            if (kind === 'register') return view.phase === 'melayani' ? 'Layani pembeli' : 'Kasir, dipakai saat warung buka';
            if (kind === 'crate') return null;
            return { supply: 'Belanja di meja pemasok', price: 'Atur harga', notice: 'Baca papan pengumuman' }[kind];
        },
        canPick(item) {
            if (view.phase === 'laporan') { talk('Hari ini sudah tutup. Lanjut ke hari berikutnya dulu.'); return false; }
            if (inStore(item) <= 0) { talk('Peti ini kosong. Belanja dulu di meja pemasok.'); return false; }
            return true;
        },
        async place(item) {
            const qty = Math.min(view.crate, inStore(item));
            const data = await call(cfg.urls.stock, { item, qty, rid: rid() });
            if (!data) return false;
            view = data.view;
            beep(740);
            sync();
            talk(`${data.result.moved} ${view.items.find((i) => i.key === item).unit} masuk rak. Pembeli hanya bisa membeli yang ada di rak.`);
            return true;
        },
        async serve(customer) {
            const data = await call(cfg.urls.serve, { customer, rid: rid() });
            if (!data) return null;
            view = data.view;
            const receipt = data.result;
            beep(receipt.short ? 440 : 1046);
            if (receipt.short) talk(`Ada ${receipt.short} barang yang tidak tersedia di rak. Isi rak dari gudang supaya pembeli tidak kecewa.`);
            sync();
            return receipt;
        },
        miss(customer) {
            call(cfg.urls.miss, { customer, rid: rid() }, { quiet: true }).then((data) => {
                if (data) { view = data.view; sync(); }
            });
            talk('Satu pembeli pergi karena terlalu lama menunggu.');
        },
        arrive() { beep(988); },
        station(kind) {
            if (kind === 'supply') {
                if (!['belanja', 'harga', 'buka'].includes(view.phase)) return talk('Pemasok hanya melayani sebelum warung buka.');
                return openSheet('supply', 'Meja pemasok', panelBuy());
            }
            if (kind === 'price') {
                if (view.phase === 'belanja') return talk('Belanja dulu di meja pemasok, baru atur harga.');
                if (!['harga', 'buka'].includes(view.phase)) return talk('Harga tidak bisa diubah saat warung buka.');
                return openSheet('price', 'Papan harga', panelPrice());
            }
            if (kind === 'notice') return openSheet('notice', 'Papan pengumuman', panelDecisions({ withOpen: false }));
            if (kind === 'register') return talk(view.phase === 'melayani' ? 'Belum ada pembeli di depan kasir.' : 'Kasir dipakai setelah papan dibalik ke BUKA.');
            if (kind === 'sign') {
                if (view.phase === 'melayani') return openSheet('close', 'Papan buka tutup', panelCloseConfirm());
                if (view.phase === 'laporan') return openSheet('report', 'Laporan', panelReport());
                if (view.phase !== 'buka') return talk(view.phase === 'belanja' ? 'Warung belum punya stok. Belanja dulu di meja pemasok.' : 'Atur harga dulu di papan harga.');
                if (!draft.special) { talk('Pilih keputusan hari ini di papan pengumuman dulu.'); return openSheet('notice', 'Papan pengumuman', panelDecisions({ withOpen: false })); }
                if (onShelf() === 0) return talk('Rak masih kosong. Bawa peti dari gudang ke rak pajang dulu.');
                return openSheet('open', 'Papan buka tutup', panelOpenConfirm());
            }
            return null;
        },
        inventory() { openSheet('stock', 'Isi warung', panelStock()); },
        pause() { if (!sheetKind) openSheet('pause', 'Menu', panelPause()); },
        padUi,
        device(next) {
            device = next;
            els.touch.classList.toggle('hidden', lite || next !== 'touch');
        },
    };

    // ---------- Kiriman tahap ----------

    async function submitBuy() {
        const qty = Object.fromEntries(view.items.map((i) => [i.key, draft.qty[i.key] || 0]));
        const data = await call(cfg.urls.buy, { qty });
        if (!data) return;
        view = data.view;
        draft.qty = {};
        beep(660);
        if (lite) return render();
        closeSheet();
        sync();
        talk('Barang masuk gudang di belakang. Atur harga di papan dinding kiri, lalu bawa petinya ke rak pajang.');
    }

    async function submitPrice() {
        const prices = Object.fromEntries(view.items.map((i) => [i.key, draft.prices[i.key] ?? i.price]));
        const data = await call(cfg.urls.price, { prices });
        if (!data) return;
        view = data.view;
        if (lite) return render();
        closeSheet();
        sync();
        talk('Harga terpasang. Cek papan pengumuman untuk keputusan hari ini.');
    }

    async function submitOpen() {
        const data = await call(cfg.urls.open, { promo: draft.promo, special: draft.special, rid: rid() });
        if (!data) return;
        view = data.view;
        closeSheet();
        sync();
        game?.startServing(view);
        beep(880);
        talk(view.flags.bus ? 'Bus rombongan datang. Jaga rak tetap terisi dan layani dengan cepat.' : 'Warung buka. Pembeli akan antre di depan kasir.');
    }

    async function submitClose() {
        const data = await call(cfg.urls.close, { rid: rid() });
        if (!data) return;
        view = data.view;
        draft.prices = {};
        sync();
        talk(view.result.bos_comment);
        openSheet('report', 'Laporan', panelReport());
    }

    async function serveAllAndClose() {
        for (const c of view.customers.filter((x) => x.status === 'menunggu')) {
            const data = await call(cfg.urls.serve, { customer: c.id, rid: rid() });
            if (data) view = data.view;
        }
        const data = await call(cfg.urls.close, { rid: rid() });
        if (!data) return;
        view = data.view;
        render();
    }

    async function submitRun() {
        const data = await call(cfg.urls.run, { promo: draft.promo, special: draft.special });
        if (!data) return;
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
        draft.special = null;
        if (lite) return render();
        closeSheet();
        sync();
        talk(view.bos_line);
    }

    // ---------- Mode ---------

    function sync() {
        hud();
        if (game && !lite) game.sync(view);
    }

    function render() {
        hud();
        if (lite) {
            const panel = { belanja: panelBuy, harga: panelPrice, buka: () => panelDecisions({ withOpen: true }), melayani: panelServeAll, laporan: panelReport }[view.phase];
            openSheet(view.phase, `Hari ${view.day}`, panel ? panel() : el('p', {}, 'Memuat'));
        }
        if (view.phase === 'laporan') talk(view.result.bos_comment);
        else if (view.phase === 'belanja') talk(view.bos_line);
    }

    function applySoundButton() {
        soundBtn.setAttribute('aria-pressed', String(sound));
        soundBtn.setAttribute('aria-label', sound ? 'Matikan efek suara' : 'Nyalakan efek suara');
        soundBtn.querySelector('[data-sound-on]').classList.toggle('hidden', !sound);
        soundBtn.querySelector('[data-sound-off]').classList.toggle('hidden', sound);
    }
    function toggleSound() {
        sound = !sound;
        store('warung-suara', sound ? '1' : '0');
        applySoundButton();
        beep(880);
    }
    function toggleLite() {
        lite = !lite;
        store('warung-ringan', lite ? '1' : '0');
        applyLite();
    }

    async function applyLite() {
        liteBtn.textContent = lite ? 'Mode 3D' : 'Mode Ringan';
        liteBtn.disabled = !hasWebGL();
        els.lite.classList.toggle('hidden', !lite);
        els.lite.classList.toggle('flex', lite);
        els.canvas.classList.toggle('hidden', lite);
        els.crosshair.classList.toggle('hidden', lite);
        els.touch.classList.toggle('hidden', lite || device !== 'touch');
        els.stage.className = `absolute inset-0 ${lite ? 'lg:right-[420px]' : ''}`;
        els.sheet.className = lite ? SHEET_LITE : `${SHEET_MODAL} hidden`;
        sheetKind = null;
        if (lite) {
            game?.pause();
            game?.setActive(false);
            render();
            return;
        }
        if (!game) {
            try {
                const mod = await import('./warung/game.js');
                game = await mod.createGame(els.canvas, { reduce, poseKey: `warung-posisi-${cfg.gameId}`, touchRoot: els.touch, hooks });
            } catch (e) {
                lite = true;
                applyLite();
                showError('Tampilan 3D tidak bisa dimuat di perangkat ini, jadi Mode Ringan dipakai.');
                return;
            }
        }
        game.resume();
        game.sync(view);
        if (view.phase === 'melayani') game.startServing(view);
        hud();
        showStart();
    }

    function showStart() {
        els.controls.replaceChildren(...controlList());
        els.start.classList.remove('hidden');
        els.start.classList.add('flex');
        game.setActive(false);
        const btn = els.start.querySelector('[data-start-btn]');
        btn.focus();
        btn.onclick = () => {
            els.start.classList.add('hidden');
            els.start.classList.remove('flex');
            game.setActive(true);
            game.lock();
            if (view.phase === 'laporan') openSheet('report', 'Laporan', panelReport());
            else talk(view.phase === 'belanja' ? view.bos_line : objectives()[0]);
        };
    }

    els.sheetClose.addEventListener('click', () => closeSheet());
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sheetKind && !lite) { e.preventDefault(); closeSheet({ relock: false }); }
    });
    liteBtn.addEventListener('click', toggleLite);
    soundBtn.addEventListener('click', toggleSound);
    setInterval(() => { if (view.phase === 'melayani' && !lite) hud(); }, 1000);

    applySoundButton();
    hud();
    playClip(els.bos, cfg.clipIdle);
    await applyLite();
}

const root = document.querySelector('[data-warung]');
if (root) initWarung(root);
