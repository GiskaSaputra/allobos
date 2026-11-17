export function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/** POST JSON dengan token CSRF. Melempar Error berisi pesan dari server bila gagal. */
export async function postJson(url, body = {}) {
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify(body),
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
        const error = new Error(firstError || data.message || 'Permintaan gagal. Periksa sambungan internet lalu coba lagi.');
        error.status = response.status;
        error.data = data;
        throw error;
    }
    return data;
}

function lockButton(button) {
    if (!button || !button.dataset.loading) return;
    const label = button.querySelector('[data-label]') ?? button;
    button.style.minWidth = `${button.offsetWidth}px`;
    label.textContent = button.dataset.loading;
    button.setAttribute('aria-busy', 'true');
    setTimeout(() => { button.disabled = true; }, 0);
}

function confirmDialog(form, submitter) {
    const dialog = document.getElementById('confirm-dialog');
    if (!dialog) return true;
    const reasonName = form.dataset.confirmReason;
    const reasonWrap = dialog.querySelector('[data-confirm-reason-wrap]');
    const reason = dialog.querySelector('[data-confirm-reason]');
    const error = dialog.querySelector('[data-confirm-error]');

    dialog.querySelector('[data-confirm-title]').textContent = form.dataset.confirmTitle || 'Lanjutkan?';
    dialog.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
    dialog.querySelector('[data-confirm-ok]').textContent = form.dataset.confirmOk || 'Ya, lanjutkan';
    reasonWrap.hidden = !reasonName;
    reason.value = '';
    error.classList.add('hidden');

    dialog.onclose = () => {
        if (dialog.returnValue !== 'ok') return;
        if (reasonName) {
            if (reason.value.trim().length < 5) {
                error.classList.remove('hidden');
                dialog.showModal();
                return;
            }
            let input = form.querySelector(`input[name="${reasonName}"]`);
            if (!input) {
                input = Object.assign(document.createElement('input'), { type: 'hidden', name: reasonName });
                form.appendChild(input);
            }
            input.value = reason.value.trim();
        }
        form.dataset.confirmed = '1';
        form.requestSubmit(submitter ?? undefined);
    };
    dialog.showModal();
    (reasonName ? reason : dialog.querySelector('[data-confirm-ok]')).focus();
    return false;
}

export function initForms() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (form.dataset.confirm && !form.dataset.confirmed) {
            event.preventDefault();
            confirmDialog(form, event.submitter);
            return;
        }
        delete form.dataset.confirmed;
        lockButton(event.submitter);
    });

    document.addEventListener('click', (event) => {
        const exit = event.target.closest('[data-exit-confirm]');
        if (exit && exit.dataset.exitConfirm && !window.confirmExitDisabled) {
            event.preventDefault();
            const form = document.createElement('form');
            form.dataset.confirm = exit.dataset.exitConfirm;
            form.dataset.confirmOk = 'Ya, keluar';
            form.method = 'GET';
            form.action = exit.href;
            document.body.appendChild(form);
            confirmDialog(form);
            return;
        }

        const copy = event.target.closest('[data-copy]');
        if (copy && navigator.clipboard) {
            navigator.clipboard.writeText(copy.dataset.copy).then(() => {
                const label = copy.querySelector('[data-copy-label]') ?? copy.querySelector('[data-label]') ?? copy;
                const old = label.textContent;
                label.textContent = 'Tersalin';
                setTimeout(() => { label.textContent = old; }, 2000);
            });
        }
    });
}
