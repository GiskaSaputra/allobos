<x-layouts.public title="Jadi Bos Pendamping" description="Pengusaha aktif dapat mendampingi warga Gunungkidul lewat sesi Temu Bos.">
    <section class="container-page pt-6">
        <div class="grid gap-10 lg:grid-cols-[1fr_1.2fr]">
            <div class="space-y-4">
                <h1 class="text-4xl sm:text-5xl">Jadi Bos pendamping</h1>
                <p class="text-fg-body">Bagikan pengalamanmu ke warga yang baru mulai. Kamu menentukan sendiri slot waktu, dan semua catatan sesi tersusun rapi.</p>
                <ul class="space-y-2 text-fg-body">
                    @foreach (App\Models\BossProfile::STANDARDS as $label)<li class="flex gap-2"><x-phosphor-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-success" aria-hidden="true" />{{ $label }}</li>@endforeach
                </ul>
                <p class="text-sm text-fg-muted">Profil diperiksa Pengelola IPI dengan empat standar di atas sebelum kamu bisa membuka slot.</p>
            </div>
            <form method="POST" action="{{ route('become-boss.store') }}" class="card space-y-5" x-data="{ agree: {{ old('consent') ? 'true' : 'false' }} }">
                @csrf
                @guest
                    <h2 class="text-xl">Akun</h2>
                    <x-field label="Nama lengkap" name="name" required><x-text-input name="name" required /></x-field>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-field label="Email" name="email" required><x-text-input name="email" type="email" required /></x-field>
                        <x-field label="Nomor WhatsApp" name="whatsapp" required><x-text-input name="whatsapp" type="tel" required /></x-field>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-field label="Kata sandi" name="password" required><x-text-input name="password" type="password" required autocomplete="new-password" /></x-field>
                        <x-field label="Ulangi kata sandi" name="password_confirmation" required><x-text-input name="password_confirmation" type="password" required autocomplete="new-password" /></x-field>
                    </div>
                @endguest
                <h2 class="text-xl">Usaha</h2>
                <x-field label="Nama usaha" name="business_name" required><x-text-input name="business_name" required /></x-field>
                <x-field label="Tentang usahamu" name="business_desc" required><x-textarea name="business_desc" rows="4" required /></x-field>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-field label="Bidang" name="business_field_id" required><x-select name="business_field_id" :options="$fields" placeholder="Pilih" required /></x-field>
                    <x-field label="Kapanewon" name="district_id"><x-select name="district_id" :options="$districts" placeholder="Di luar Gunungkidul" /></x-field>
                    <x-field label="Lama usaha (tahun)" name="years_running" required><x-text-input name="years_running" type="number" min="0" value="0" required /></x-field>
                </div>
                <x-field label="Rekam jejak" name="track_record"><x-textarea name="track_record" rows="3" /></x-field>
                <x-field label="Tautan yang bisa ditelusuri" name="trace_url"><x-text-input name="trace_url" type="url" /></x-field>
                @guest
                    <label class="flex items-start gap-3 text-sm text-fg-body"><input type="checkbox" name="consent" value="1" class="field-check mt-0.5" x-model="agree"><span>Saya setuju dengan <a href="{{ route('pages.privasi') }}" class="text-accent-text underline">kebijakan privasi</a> dan <a href="{{ route('pages.syarat') }}" class="text-accent-text underline">syarat pemakaian</a>.</span></label>
                    @error('consent')<p class="text-sm text-danger">{{ $message }}</p>@enderror
                    <x-button variant="primary" loading="Mengajukan" x-bind:disabled="!agree">Ajukan Profil Bos</x-button>
                @else
                    <x-button variant="primary" loading="Mengajukan">Ajukan Profil Bos</x-button>
                @endguest
            </form>
        </div>
    </section>
</x-layouts.public>
