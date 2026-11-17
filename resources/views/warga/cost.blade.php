<x-layouts.app title="Kalkulator HPP">
    <x-page-header title="Kalkulator HPP dan titik impas" description="HPP adalah biaya membuat satu barang. Titik impas adalah jumlah jualan sebulan supaya semua biaya tertutup. Hitungan ini berjalan tanpa AI.">
        <x-slot:actions><x-button variant="ghost" :href="route('warga.plan.show')" icon="phosphor-arrow-left">Kembali ke Rencana</x-button></x-slot:actions>
    </x-page-header>
    <livewire:cost-calculator />
</x-layouts.app>
