@php($error = session('error') ?? ($errors->any() && ! request()->routeIs('login', 'register', 'password.*') ? 'Ada isian yang perlu diperbaiki. Lihat pesan merah di bawah tiap isian.' : null))
@if (session('status') || $error)
    <div class="pointer-events-none fixed inset-x-0 top-20 z-flash flex justify-center px-4" role="status" aria-live="polite">
        <div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms
             @if (! $error) x-init="setTimeout(() => show = false, 5000)" @endif
             @class(['pointer-events-auto flex max-w-lg items-start gap-3 rounded-[20px] px-5 py-4 shadow-float', 'bg-danger text-status-fg' => $error, 'bg-brand text-brand-fg' => ! $error])>
            @if ($error)
                <x-phosphor-warning-circle class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true" />
            @else
                <x-phosphor-check-circle class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true" />
            @endif
            <p class="text-sm font-medium">{{ $error ?? session('status') }}</p>
            <button type="button" @click="show = false" class="-mr-1 ml-2 shrink-0 rounded-full p-1 hover:bg-ink/20" aria-label="Tutup pesan"><x-phosphor-x class="h-4 w-4" aria-hidden="true" /></button>
        </div>
    </div>
@endif
