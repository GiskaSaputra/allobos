<?php

namespace App\Services\Game;

/** Kalimat Bos Allo di awal dan akhir tiap hari. Disusun dari aturan, tanpa AI, supaya cepat. */
class BossLines
{
    public const MORNING = [
        1 => 'Hari pertama. Belanja secukupnya dulu, sisakan kas untuk biaya harian.',
        2 => 'Langit gelap sejak subuh. Pikirkan barang mana yang tahan basah sebelum belanja.',
        3 => 'Ada warung baru di seberang. Jangan buru-buru ikut murah, lihat dulu kekuatanmu.',
        4 => 'Kabarnya rombongan bus lewat hari ini. Stok yang kurang berarti pembeli pulang dengan tangan kosong.',
        5 => 'Harga bahan naik. Hitung ulang biaya per barang sebelum menentukan harga jual.',
        6 => 'Hari ini orang membicarakan warungmu. Apa yang kamu lakukan kemarin mulai terasa.',
        7 => 'Hari pasaran dan awal liburan. Pakai semua yang sudah kamu pelajari.',
    ];

    public function evening(array $result, int $target): string
    {
        $lostRatio = $result['demand_total'] ? $result['lost_total'] / $result['demand_total'] : 0;

        return match (true) {
            $result['profit'] < 0 => 'Hari ini rugi Rp'.$this->rp(abs($result['profit'])).'. Lihat biaya mana yang paling besar, lalu tekan besok.',
            $lostRatio > 0.25 => $result['lost_total'].' pembeli tidak kebagian barang. Stokmu kurang untuk hari seramai ini.',
            $result['waste_value'] > $result['revenue'] * 0.15 => 'Ada barang terbuang senilai Rp'.$this->rp($result['waste_value']).'. Belanja barang cepat basi lebih sedikit.',
            $result['satisfaction_after'] < $result['satisfaction_before'] - 5 => 'Untung masuk, tapi pelanggan kurang puas. Ingat, ulasan bisa menyebar.',
            $result['profit'] >= $target => 'Untung Rp'.$this->rp($result['profit']).', di atas target harian. Keputusan yang rapi.',
            default => 'Untung Rp'.$this->rp($result['profit']).'. Belum besar, tapi warung mulai bernapas.',
        };
    }

    private function rp(int|float $n): string
    {
        return number_format($n, 0, ',', '.');
    }
}
