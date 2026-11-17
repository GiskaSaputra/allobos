{{-- Di bawah md tiap baris menjadi kartu. Isi data-label di tiap td. --}}
<div {{ $attributes->class('table-wrap') }}>
    <table class="table-cards">{{ $slot }}</table>
</div>
