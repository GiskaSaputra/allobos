<x-layouts.dashboard title="Profil Bos">
    <x-page-header title="Profil Bos" :description="$boss->exists ? 'Status kurasi: '.$boss->status->label().'.' : 'Isi profil usahamu untuk dikurasi Pengelola IPI.'" />
    <form method="POST" action="{{ route('bos.profile.update') }}" enctype="multipart/form-data" class="card max-w-2xl space-y-5">
        @csrf @method('PATCH')
        <x-field label="Nama usaha" name="business_name" required><x-text-input name="business_name" :value="$boss->business_name" required /></x-field>
        <x-field label="Tentang usahamu" name="business_desc" hint="Apa yang kamu jalankan sekarang dan apa yang bisa kamu bagikan ke warga." required><x-textarea name="business_desc" :value="$boss->business_desc" rows="5" required /></x-field>
        <div class="grid gap-5 sm:grid-cols-3">
            <x-field label="Bidang" name="business_field_id" required><x-select name="business_field_id" :options="$fields" :value="$boss->business_field_id" placeholder="Pilih bidang" required /></x-field>
            <x-field label="Kapanewon" name="district_id"><x-select name="district_id" :options="$districts" :value="$boss->district_id" placeholder="Di luar Gunungkidul" /></x-field>
            <x-field label="Lama usaha (tahun)" name="years_running" required><x-text-input name="years_running" type="number" min="0" :value="$boss->years_running ?? 0" required /></x-field>
        </div>
        <x-field label="Rekam jejak" name="track_record"><x-textarea name="track_record" :value="$boss->track_record" rows="3" /></x-field>
        <x-field label="Tautan yang bisa ditelusuri" name="trace_url" hint="Situs usaha, media sosial, atau berita."><x-text-input name="trace_url" type="url" :value="$boss->trace_url" /></x-field>
        <x-field label="Foto" name="photo"><input id="f-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-fg-body file:mr-3 file:rounded-full file:border-0 file:bg-brand file:px-4 file:py-2 file:text-brand-fg"></x-field>
        <x-button variant="primary">{{ $boss->status === App\Enums\BossStatus::Ditolak ? 'Ajukan Ulang Profil' : 'Simpan Profil' }}</x-button>
    </form>
</x-layouts.dashboard>
