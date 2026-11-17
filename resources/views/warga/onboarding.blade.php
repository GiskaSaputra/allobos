<x-layouts.auth title="Kenali usahamu">
    <div class="mb-6 flex items-center justify-between">
        <p class="label-caps">Profil Usaha</p>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-sm text-fg-muted underline underline-offset-2 hover:text-fg">Keluar</button></form>
    </div>
    <x-step-progress :steps="App\Models\ResidentProfile::STEPS" :current="$step" />

    <form method="POST" action="{{ route('warga.onboarding.store', $step) }}" class="mt-8 space-y-6"
          x-data="{
              district: '{{ old('district_id', $profile->district_id) }}',
              status: '{{ old('business_status', $profile->business_status) }}',
              business: @js(old('existing_business', $profile->existing_business ?? '')),
              interests: @js(array_map('strval', old('interests', $profile->interests ?? []))),
              capital: '{{ old('capital_range', $profile->capital_range) }}',
              time: '{{ old('free_time', $profile->free_time) }}',
              skills: @js(old('skills', $profile->skills ?? '')),
              get filled() {
                  return {{ $step }} === 1 ? this.district !== ''
                      : {{ $step }} === 2 ? (this.status === 'belum' || (this.status === 'sudah' && this.business.trim() !== ''))
                      : {{ $step }} === 3 ? this.interests.length > 0
                      : {{ $step }} === 4 ? (this.capital !== '' && this.time !== '')
                      : this.skills.trim().length >= 3;
              }
          }">
        @csrf
        @switch($step)
            @case(1)
                <fieldset class="space-y-4">
                    <legend><h1 class="text-3xl">Kamu tinggal di kapanewon mana?</h1><p class="mt-2 text-fg-muted">Bos Allo memakai potensi lokal kapanewonmu saat memberi saran.</p></legend>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach ($districts as $district)
                            <label class="block">
                                <input type="radio" name="district_id" value="{{ $district->id }}" class="segment-input peer sr-only" x-model="district">
                                <span class="flex min-h-[48px] cursor-pointer items-center rounded-full border border-line px-4 text-sm font-medium text-fg-body transition peer-checked:border-brand peer-checked:bg-brand peer-checked:text-brand-fg peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-accent-text">{{ $district->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('district_id')<p class="text-sm text-danger">{{ $message }}</p>@enderror
                </fieldset>
                @break
            @case(2)
                <fieldset class="space-y-4">
                    <legend><h1 class="text-3xl">Sudah punya usaha?</h1></legend>
                    <div class="grid gap-2">
                        @foreach (['belum' => ['Belum', 'Saya masih mencari usaha yang cocok'], 'sudah' => ['Sudah', 'Saya sudah berjualan atau membuka jasa']] as $value => [$label, $hint])
                            <label class="block">
                                <input type="radio" name="business_status" value="{{ $value }}" class="peer sr-only" x-model="status">
                                <span class="block cursor-pointer rounded-[24px] border border-line p-4 transition peer-checked:border-brand peer-checked:bg-brand/10 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-accent-text">
                                    <span class="block font-medium text-fg">{{ $label }}</span><span class="block text-sm text-fg-muted">{{ $hint }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('business_status')<p class="text-sm text-danger">{{ $message }}</p>@enderror
                    <div x-show="status === 'sudah'" x-cloak>
                        <x-field label="Usaha apa yang sudah berjalan?" name="existing_business" hint="Contoh: keripik singkong titip warung">
                            <x-text-input name="existing_business" x-model="business" maxlength="120" />
                        </x-field>
                    </div>
                </fieldset>
                @break
            @case(3)
                <fieldset class="space-y-4">
                    <legend><h1 class="text-3xl">Bidang apa yang kamu minati?</h1><p class="mt-2 text-fg-muted">Pilih satu sampai tiga bidang usaha.</p></legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($fields as $field)
                            <label>
                                <input type="checkbox" name="interests[]" value="{{ $field->id }}" class="peer sr-only" x-model="interests" :disabled="interests.length >= 3 && !interests.includes('{{ $field->id }}')">
                                <span class="chip cursor-pointer peer-checked:border-brand peer-checked:bg-brand peer-checked:text-brand-fg peer-disabled:cursor-not-allowed peer-disabled:opacity-50 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-accent-text">{{ $field->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-sm text-fg-muted"><span x-text="interests.length">0</span> dari 3 dipilih</p>
                    @error('interests')<p class="text-sm text-danger">{{ $message }}</p>@enderror
                </fieldset>
                @break
            @case(4)
                <h1 class="text-3xl">Modal dan waktu luang</h1>
                @foreach (['capital_range' => ['Berapa modal yang bisa kamu siapkan?', App\Models\ResidentProfile::CAPITAL, 'capital'], 'free_time' => ['Berapa waktu luangmu untuk usaha?', App\Models\ResidentProfile::FREE_TIME, 'time']] as $name => [$question, $options, $model])
                    <fieldset class="space-y-3">
                        <legend class="font-medium text-fg">{{ $question }}</legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($options as $value => $label)
                                <label class="block">
                                    <input type="radio" name="{{ $name }}" value="{{ $value }}" class="peer sr-only" x-model="{{ $model }}">
                                    <span class="flex min-h-[48px] cursor-pointer items-center rounded-full border border-line px-4 text-sm font-medium text-fg-body transition peer-checked:border-brand peer-checked:bg-brand peer-checked:text-brand-fg peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-accent-text">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error($name)<p class="text-sm text-danger">{{ $message }}</p>@enderror
                    </fieldset>
                @endforeach
                @break
            @case(5)
                <h1 class="text-3xl">Apa keahlianmu?</h1>
                <x-field label="Tulis hal yang bisa kamu kerjakan" name="skills" hint="Contoh: memasak, menjahit, merawat kambing, memakai HP untuk jualan.">
                    <x-textarea name="skills" rows="4" maxlength="500" x-model="skills" />
                </x-field>
                @break
        @endswitch

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center sm:justify-between">
            @if ($step > 1)
                <x-button variant="ghost" :href="route('warga.onboarding.show', $step - 1)" icon="phosphor-arrow-left">Kembali</x-button>
            @else
                <span></span>
            @endif
            <x-button variant="primary" x-bind:disabled="!filled" :loading="$step === 5 ? 'Menyimpan' : 'Menyimpan'">{{ $step === 5 ? 'Simpan Profil' : 'Lanjut' }}</x-button>
        </div>
        <p class="text-sm text-fg-muted sm:text-right" x-show="!filled">Isi langkah ini dulu untuk melanjutkan.</p>
    </form>
</x-layouts.auth>
