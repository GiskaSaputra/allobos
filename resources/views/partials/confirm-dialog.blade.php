<dialog id="confirm-dialog" class="w-[min(92vw,440px)] rounded-card border border-line bg-surface-raised p-0 text-fg-body shadow-float backdrop:bg-ink/60">
    <form method="dialog" class="space-y-4 p-6">
        <h2 class="text-xl" data-confirm-title>Lanjutkan?</h2>
        <p class="text-fg-body" data-confirm-message></p>
        <div data-confirm-reason-wrap hidden class="space-y-2">
            <label for="confirm-reason" class="block text-sm font-medium text-fg">Alasan</label>
            <textarea id="confirm-reason" rows="3" maxlength="500" class="field-input" data-confirm-reason></textarea>
            <p class="hidden text-sm text-danger" data-confirm-error>Tulis alasannya dulu, minimal 5 karakter.</p>
        </div>
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button value="cancel" class="btn btn-ghost">Batal</button>
            <button value="ok" class="btn btn-danger" data-confirm-ok>Ya, lanjutkan</button>
        </div>
    </form>
</dialog>
