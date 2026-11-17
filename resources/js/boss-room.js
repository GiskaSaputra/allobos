import { postJson } from './forms';
import { playClip } from './avatar';

const root = document.querySelector('[data-boss-room]');
if (root) initBossRoom(root);

function initBossRoom(root) {
    const cfg = JSON.parse(root.dataset.config);
    const $ = (sel) => root.querySelector(sel);
    const els = {
        avatar: $('[data-avatar]'), status: $('[data-status]'), log: $('[data-log]'), input: $('[data-input]'),
        talk: $('[data-talk]'), send: $('[data-send]'), clear: $('[data-clear]'), hint: $('[data-hint]'),
        mute: $('[data-mute]'), replay: $('[data-replay]'), notice: $('[data-notice]'), left: $('[data-left]'),
        error: $('[data-error]'), errorText: $('[data-error-text]'), retry: $('[data-retry]'), composer: $('[data-composer]'),
    };
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const canSpeak = 'speechSynthesis' in window;
    const statusText = {
        idle: 'Bos Allo siap mendengar',
        listening: 'Mendengarkan kamu',
        thinking: 'Bos Allo sedang berpikir',
        speaking: 'Bos Allo sedang berbicara',
        error: 'Ada kendala',
    };

    let state = 'idle';
    let mode = cfg.mode;
    let muted = readMuted();
    let lastReply = '';
    let pendingMessage = null;
    let recognition = null;
    let finalText = '';
    let listening = false;
    let typingTimer = null;
    let voice = null;
    let blocked = false;

    function setState(next) {
        state = next;
        root.dataset.state = next;
        playClip(els.avatar, next === 'speaking' ? cfg.clipTalk : cfg.clipIdle);
        els.status.textContent = statusText[next];
        const busy = next === 'thinking' || next === 'speaking';
        els.talk.disabled = busy || mode !== 'suara' || blocked;
        els.talk.classList.toggle('pulse-ring', next === 'listening');
        refreshSend();
    }

    function refreshSend() {
        els.send.disabled = blocked || state === 'thinking' || !els.input.value.trim();
    }

    function notice(text) {
        els.notice.textContent = text;
        els.notice.classList.remove('hidden');
    }

    function readMuted() {
        try { return localStorage.getItem('allo-bisu') === '1'; } catch (e) { return false; }
    }

    function applyMute() {
        els.mute.setAttribute('aria-pressed', String(muted));
        els.mute.setAttribute('aria-label', muted ? 'Nyalakan suara Bos Allo' : 'Bisukan suara Bos Allo');
        els.mute.querySelector('[data-icon-on]').classList.toggle('hidden', muted);
        els.mute.querySelector('[data-icon-off]').classList.toggle('hidden', !muted);
        try { localStorage.setItem('allo-bisu', muted ? '1' : '0'); } catch (e) { /* tanpa penyimpanan, pilihan berlaku di halaman ini saja */ }
    }

    function pickVoice() {
        if (!canSpeak) return;
        const voices = speechSynthesis.getVoices();
        voice = voices.find((v) => v.lang === 'id-ID') || voices.find((v) => v.lang?.toLowerCase().startsWith('id')) || null;
    }

    function bubble(role, text) {
        const li = document.createElement('li');
        li.className = role === 'bos' ? 'flex gap-3' : 'flex justify-end';
        const box = document.createElement('div');
        box.className = role === 'bos'
            ? 'max-w-[85%] rounded-[22px] rounded-tl-md bg-surface-raised px-4 py-3 text-fg-body shadow-card'
            : 'max-w-[85%] rounded-[22px] rounded-tr-md bg-brand px-4 py-3 text-brand-fg';
        const who = document.createElement('p');
        who.className = 'sr-only';
        who.textContent = role === 'bos' ? 'Bos Allo:' : 'Kamu:';
        const body = document.createElement('p');
        body.className = 'whitespace-pre-line';
        body.textContent = text;
        box.append(who, body);
        li.append(box);
        els.log.append(li);
        els.log.scrollTop = els.log.scrollHeight;
        return { box, body };
    }

    function addModules(box, modules) {
        const links = (modules || []).filter((m) => m.url);
        if (!links.length) return;
        const wrap = document.createElement('div');
        wrap.className = 'mt-3 flex flex-wrap gap-2';
        links.forEach((m) => {
            const a = document.createElement('a');
            a.href = m.url;
            a.className = 'chip';
            a.textContent = m.label;
            wrap.append(a);
        });
        box.append(wrap);
    }

    function composeReply(data) {
        return data.follow_up ? `${data.reply}\n\n${data.follow_up}` : data.reply;
    }

    // Teks ditampilkan per kata selama GIF bicara bila suara tidak dipakai.
    function typeOut(target, text) {
        return new Promise((resolve) => {
            if (reduce) {
                target.textContent = text;
                resolve();
                return;
            }
            const words = text.split(/(\s+)/);
            let i = 0;
            target.textContent = '';
            clearInterval(typingTimer);
            typingTimer = setInterval(() => {
                target.textContent += words[i] ?? '';
                i += 1;
                els.log.scrollTop = els.log.scrollHeight;
                if (i >= words.length) {
                    clearInterval(typingTimer);
                    resolve();
                }
            }, 45);
        });
    }

    // Kalimat dibacakan bergiliran karena ujaran panjang sering terpotong di Chrome.
    function speak(text) {
        return new Promise((resolve) => {
            if (muted || !canSpeak) {
                resolve(false);
                return;
            }
            speechSynthesis.cancel();
            const parts = text.match(/[^.!?\n]+[.!?]*/g)?.map((p) => p.trim()).filter(Boolean) ?? [text];
            let started = false;
            let finished = false;
            const finish = (ok) => {
                if (finished) return;
                finished = true;
                resolve(ok);
            };
            parts.forEach((part, index) => {
                const u = new SpeechSynthesisUtterance(part);
                u.lang = 'id-ID';
                if (voice) u.voice = voice;
                u.rate = 1;
                u.onstart = () => {
                    started = true;
                    setState('speaking');
                };
                u.onerror = () => finish(started);
                if (index === parts.length - 1) u.onend = () => finish(true);
                speechSynthesis.speak(u);
            });
            // Peramban yang menolak suara tanpa interaksi tidak pernah memicu onstart.
            setTimeout(() => { if (!started) { speechSynthesis.cancel(); finish(false); } }, 1500);
        });
    }

    async function present(text, modules) {
        lastReply = text;
        els.replay.disabled = false;
        const { box, body } = bubble('bos', '');
        setState('speaking');
        const spoken = speak(text);
        await typeOut(body, text);
        addModules(box, modules);
        await spoken;
        setState('idle');
    }

    function showError(message) {
        els.errorText.textContent = message;
        els.error.classList.remove('hidden');
        setState('error');
    }

    function hideError() {
        els.error.classList.add('hidden');
    }

    async function sendMessage(message, isRetry = false) {
        if (!message || state === 'thinking') return;
        stopListening();
        hideError();
        if (!isRetry) {
            bubble('warga', message);
            els.input.value = '';
            finalText = '';
            els.clear.classList.add('hidden');
        }
        pendingMessage = message;
        setState('thinking');
        try {
            const data = await postJson(cfg.sendUrl, { message });
            pendingMessage = null;
            if (data.turns_left !== null && data.turns_left !== undefined) {
                els.left.textContent = `Sisa ${data.turns_left} giliran hari ini`;
            }
            await present(composeReply(data), data.modules);
        } catch (error) {
            if (error.status === 429 || error.status === 409) {
                blocked = true;
                els.retry.classList.add('hidden');
            }
            showError(error.message);
        }
    }

    function setupRecognition() {
        if (!Recognition) return null;
        const r = new Recognition();
        r.lang = 'id-ID';
        r.interimResults = true;
        r.continuous = true;
        r.onresult = (event) => {
            let interim = '';
            for (let i = event.resultIndex; i < event.results.length; i += 1) {
                const chunk = event.results[i][0].transcript;
                if (event.results[i].isFinal) finalText += `${chunk} `;
                else interim += chunk;
            }
            els.input.value = (finalText + interim).trim();
            els.clear.classList.toggle('hidden', !els.input.value);
            refreshSend();
        };
        r.onerror = (event) => {
            listening = false;
            if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                switchToText('Izin mikrofon ditolak, jadi mode teks dipakai. Kamu bisa mengizinkan mikrofon lewat pengaturan peramban lalu memuat ulang halaman.');
            } else if (event.error !== 'aborted' && event.error !== 'no-speech') {
                notice('Suara belum tertangkap. Coba bicara lebih dekat ke mikrofon, atau ketik pertanyaanmu.');
            }
            if (state === 'listening') setState('idle');
        };
        r.onend = () => {
            listening = false;
            if (state === 'listening') setState('idle');
        };
        return r;
    }

    function startListening() {
        if (mode !== 'suara' || listening || state === 'speaking' || state === 'thinking' || !recognition) return;
        if (canSpeak) speechSynthesis.cancel();
        finalText = els.input.value ? `${els.input.value} ` : '';
        try {
            recognition.start();
            listening = true;
            hideError();
            setState('listening');
        } catch (e) {
            listening = false;
        }
    }

    function stopListening() {
        if (listening && recognition) recognition.stop();
    }

    function switchToText(message) {
        mode = 'teks';
        els.talk.classList.add('hidden');
        els.hint.textContent = 'Ketik pertanyaanmu lalu tekan Kirim.';
        notice(message);
        setState(state === 'listening' ? 'idle' : state);
    }

    // Tahan untuk bicara dengan penunjuk. Papan ketik dan pembaca layar memakai klik untuk mulai dan berhenti.
    els.talk.addEventListener('pointerdown', (e) => {
        if (e.button !== 0) return;
        e.preventDefault();
        els.talk.setPointerCapture?.(e.pointerId);
        startListening();
    });
    ['pointerup', 'pointercancel'].forEach((type) => els.talk.addEventListener(type, () => stopListening()));
    els.talk.addEventListener('click', (e) => {
        if (e.detail !== 0) return;
        if (listening) stopListening(); else startListening();
    });

    els.composer.addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage(els.input.value.trim());
    });
    els.input.addEventListener('input', () => {
        els.clear.classList.toggle('hidden', !els.input.value || mode !== 'suara');
        refreshSend();
    });
    els.input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey && window.matchMedia('(pointer: fine)').matches) {
            e.preventDefault();
            sendMessage(els.input.value.trim());
        }
    });
    els.clear.addEventListener('click', () => {
        stopListening();
        els.input.value = '';
        finalText = '';
        els.clear.classList.add('hidden');
        refreshSend();
        els.input.focus();
    });
    els.retry.addEventListener('click', () => sendMessage(pendingMessage, true));
    els.mute.addEventListener('click', () => {
        muted = !muted;
        applyMute();
        if (muted && canSpeak) {
            speechSynthesis.cancel();
        }
    });
    els.replay.addEventListener('click', async () => {
        if (!lastReply || state === 'thinking') return;
        if (muted || !canSpeak) {
            notice('Suara sedang dibisukan atau tidak tersedia di peramban ini. Jawaban tetap ada sebagai teks.');
            return;
        }
        setState('speaking');
        await speak(lastReply);
        setState('idle');
    });
    root.closest('body').querySelector('[data-finish]')?.addEventListener('submit', () => {
        stopListening();
        if (canSpeak) speechSynthesis.cancel();
        window.confirmExitDisabled = true;
    });

    if (canSpeak) {
        pickVoice();
        speechSynthesis.onvoiceschanged = pickVoice;
    }
    applyMute();

    if (mode === 'suara') {
        recognition = setupRecognition();
        if (!recognition) switchToText('Peramban ini belum mendukung pengenalan suara, jadi mode teks dipakai.');
    } else {
        els.talk.classList.add('hidden');
        els.hint.textContent = 'Ketik pertanyaanmu lalu tekan Kirim.';
    }
    if (!canSpeak) notice('Peramban ini tidak bisa membacakan jawaban. Semua jawaban tetap tampil sebagai teks.');

    cfg.turns.forEach((turn) => {
        const { box } = bubble(turn.role, turn.role === 'bos' && turn.follow_up ? `${turn.text}\n\n${turn.follow_up}` : turn.text);
        if (turn.role === 'bos') {
            addModules(box, turn.modules);
            lastReply = turn.text;
            els.replay.disabled = false;
        }
    });

    setState('idle');

    if (cfg.status === 'disiapkan' || !cfg.turns.length) {
        setState('thinking');
        postJson(cfg.startUrl)
            .then((data) => present(data.greeting, []))
            .catch((error) => {
                blocked = true;
                showError(error.message);
            });
    }
}
