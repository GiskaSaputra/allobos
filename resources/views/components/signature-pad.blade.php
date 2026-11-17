@props(['name' => 'drawing'])
<div data-signature-pad class="space-y-3">
    <div class="relative overflow-hidden rounded-[20px] border border-line bg-surface-raised">
        <canvas class="block h-[180px] w-full touch-none" aria-label="Kanvas tanda tangan. Gambar dengan jari, pena, atau tetikus." role="img"></canvas>
        <span class="pointer-events-none absolute inset-x-6 bottom-10 border-b border-dashed border-line" aria-hidden="true"></span>
    </div>
    <input type="hidden" name="{{ $name }}" data-signature-output>
    <div class="flex flex-wrap gap-2">
        <button type="button" class="btn btn-ghost btn-sm" data-signature-clear>Hapus</button>
    </div>
</div>
