<?php

namespace App\Services\Game;

/** Data tetap permainan: jenis warung, tujuh babak, promosi, dan keputusan khusus tiap babak. */
class GameRules
{
    public const KINDS = [
        'singkong' => [
            'label' => 'Warung olahan singkong',
            'cash' => 300000,
            'fixed' => 15000,
            'items' => [
                'keripik' => ['name' => 'Keripik singkong', 'unit' => 'bungkus', 'cost' => 4000, 'fair' => 7000, 'demand' => 16, 'spoil' => 0.0, 'wet' => 0.25],
                'tiwul' => ['name' => 'Tiwul', 'unit' => 'porsi', 'cost' => 3000, 'fair' => 6000, 'demand' => 10, 'spoil' => 1.0, 'wet' => 1.0],
                'gethuk' => ['name' => 'Gethuk', 'unit' => 'porsi', 'cost' => 2500, 'fair' => 5000, 'demand' => 11, 'spoil' => 1.0, 'wet' => 1.0],
                'es_teh' => ['name' => 'Es teh', 'unit' => 'gelas', 'cost' => 1000, 'fair' => 3000, 'demand' => 18, 'spoil' => 0.5, 'wet' => 0.5],
            ],
        ],
        'kelontong' => [
            'label' => 'Warung kelontong',
            'cash' => 400000,
            'fixed' => 20000,
            'items' => [
                'air' => ['name' => 'Air mineral', 'unit' => 'botol', 'cost' => 2500, 'fair' => 4000, 'demand' => 22, 'spoil' => 0.0, 'wet' => 0.0],
                'mi' => ['name' => 'Mi instan', 'unit' => 'bungkus', 'cost' => 2800, 'fair' => 4000, 'demand' => 12, 'spoil' => 0.0, 'wet' => 0.0],
                'roti' => ['name' => 'Roti', 'unit' => 'bungkus', 'cost' => 3000, 'fair' => 5000, 'demand' => 9, 'spoil' => 0.3, 'wet' => 0.5],
                'telur' => ['name' => 'Telur', 'unit' => 'butir', 'cost' => 1800, 'fair' => 2500, 'demand' => 15, 'spoil' => 0.05, 'wet' => 0.05],
            ],
        ],
        'oleh_oleh' => [
            'label' => 'Warung oleh-oleh wisata',
            'cash' => 600000,
            'fixed' => 25000,
            'items' => [
                'belalang' => ['name' => 'Belalang goreng', 'unit' => 'toples', 'cost' => 15000, 'fair' => 25000, 'demand' => 6, 'spoil' => 0.05, 'wet' => 0.1],
                'peyek' => ['name' => 'Peyek kacang', 'unit' => 'bungkus', 'cost' => 6000, 'fair' => 10000, 'demand' => 8, 'spoil' => 0.02, 'wet' => 0.2],
                'kaos' => ['name' => 'Kaos pantai', 'unit' => 'potong', 'cost' => 35000, 'fair' => 55000, 'demand' => 3, 'spoil' => 0.0, 'wet' => 0.0],
                'topeng' => ['name' => 'Gantungan topeng', 'unit' => 'buah', 'cost' => 5000, 'fair' => 12000, 'demand' => 6, 'spoil' => 0.0, 'wet' => 0.0],
            ],
        ],
    ];

    /** Batas efek tiap babak. Kejadian dari AI dijepit ke rentang ini supaya tema tetap terjaga. */
    public const THEMES = [
        1 => ['title' => 'Buka pertama', 'lesson' => 'Belanja stok secukupnya, jangan menghabiskan modal', 'demand' => [0.85, 1.0], 'cost' => [1.0, 1.0], 'cash' => [0, 0]],
        2 => ['title' => 'Hujan seharian', 'lesson' => 'Permintaan turun dan stok bisa basah', 'demand' => [0.45, 0.65], 'cost' => [1.0, 1.05], 'cash' => [-10000, 0], 'rain' => true],
        3 => ['title' => 'Pesaing baru di seberang', 'lesson' => 'Perang harga atau beda penawaran', 'demand' => [0.7, 0.85], 'cost' => [1.0, 1.0], 'cash' => [0, 0], 'competitor' => true],
        4 => ['title' => 'Rombongan bus wisata', 'lesson' => 'Stok kurang berarti kehilangan omzet', 'demand' => [1.6, 1.9], 'cost' => [1.0, 1.0], 'cash' => [0, 15000], 'bus' => true],
        5 => ['title' => 'Harga bahan naik', 'lesson' => 'Hitung ulang HPP dan harga jual', 'demand' => [0.9, 1.0], 'cost' => [1.2, 1.3], 'cash' => [0, 0]],
        6 => ['title' => 'Ulasan pelanggan menyebar', 'lesson' => 'Mutu dan pelayanan kemarin menentukan hari ini', 'demand' => [0.95, 1.05], 'cost' => [1.0, 1.0], 'cash' => [-5000, 5000]],
        7 => ['title' => 'Hari pasaran dan awal liburan', 'lesson' => 'Menggabungkan semua pelajaran', 'demand' => [1.4, 1.7], 'cost' => [1.0, 1.0], 'cash' => [0, 20000], 'bus' => true],
    ];

    public const PROMOS = [
        'tanpa' => ['label' => 'Tanpa promosi', 'cost' => 0, 'demand' => 1.0, 'price' => 1.0, 'sat' => 0, 'hint' => 'Hemat biaya'],
        'spanduk' => ['label' => 'Pasang spanduk', 'cost' => 10000, 'demand' => 1.12, 'price' => 1.0, 'sat' => 0, 'hint' => 'Lebih banyak yang mampir'],
        'diskon' => ['label' => 'Diskon 10 persen', 'cost' => 0, 'demand' => 1.2, 'price' => 0.9, 'sat' => 2, 'hint' => 'Laku lebih cepat, untung per barang turun'],
    ];

    public const SPECIALS = [
        1 => ['question' => 'Hari pertama. Warung simbah perlu dibenahi?', 'options' => [
            'rapi' => ['label' => 'Rapikan dan bersihkan sendiri', 'cost' => 0, 'demand' => 1.0, 'sat' => 3, 'hint' => 'Gratis, pembeli lebih nyaman'],
            'dekor' => ['label' => 'Cat ulang dan pasang hiasan', 'cost' => 40000, 'demand' => 1.08, 'sat' => 6, 'hint' => 'Rp40.000, menarik tapi menguras modal'],
            'biarkan' => ['label' => 'Biarkan apa adanya', 'cost' => 0, 'demand' => 0.95, 'sat' => -2, 'hint' => 'Tidak ada biaya'],
        ]],
        2 => ['question' => 'Hujan turun sejak pagi. Apa yang kamu lakukan?', 'options' => [
            'tenda' => ['label' => 'Pasang tenda plastik', 'cost' => 15000, 'demand' => 1.35, 'sat' => 2, 'wet' => 0.3, 'hint' => 'Rp15.000, pembeli tetap mampir dan stok terlindung'],
            'antar' => ['label' => 'Terima pesanan lewat WhatsApp dan antar', 'cost' => 10000, 'demand' => 1.25, 'sat' => 4, 'hint' => 'Rp10.000 bensin, pelanggan senang'],
            'tutup' => ['label' => 'Tutup lebih awal', 'cost' => 0, 'demand' => 0.8, 'sat' => 0, 'fixed' => 0.5, 'hint' => 'Biaya tetap berkurang separuh'],
        ]],
        3 => ['question' => 'Warung baru buka di seberang jalan dengan harga murah. Responsmu?', 'options' => [
            'banting' => ['label' => 'Turunkan harga 15 persen', 'cost' => 0, 'demand' => 1.15, 'price' => 0.85, 'sat' => 0, 'hint' => 'Perang harga, untung tipis'],
            'beda' => ['label' => 'Beri bonus dan paket hemat', 'cost' => 10000, 'demand' => 1.12, 'sat' => 6, 'hint' => 'Rp10.000, beda penawaran'],
            'diam' => ['label' => 'Diamkan saja', 'cost' => 0, 'demand' => 0.9, 'sat' => 0, 'hint' => 'Sebagian pembeli pindah'],
        ]],
        4 => ['question' => 'Rombongan bus wisata berhenti di depan warung. Harga bagaimana?', 'options' => [
            'naik' => ['label' => 'Naikkan harga 20 persen', 'cost' => 0, 'demand' => 0.95, 'price' => 1.2, 'sat' => -4, 'later' => [6 => -12], 'hint' => 'Untung hari ini naik, tapi bisa jadi omongan'],
            'normal' => ['label' => 'Harga tetap dan layani cepat', 'cost' => 0, 'demand' => 1.0, 'sat' => 4, 'later' => [6 => 4], 'hint' => 'Pembeli puas dan bercerita'],
            'paket' => ['label' => 'Buat paket rombongan', 'cost' => 5000, 'demand' => 1.1, 'price' => 0.95, 'sat' => 3, 'later' => [6 => 2], 'hint' => 'Rp5.000 kemasan, laku lebih banyak'],
        ]],
        5 => ['question' => 'Harga bahan naik sejak pagi. Langkahmu?', 'options' => [
            'naik_sedikit' => ['label' => 'Naikkan harga jual 10 persen', 'cost' => 0, 'demand' => 0.95, 'price' => 1.1, 'sat' => -1, 'hint' => 'Untung terjaga, sedikit pembeli mundur'],
            'porsi' => ['label' => 'Kecilkan porsi atau isi', 'cost' => 0, 'demand' => 1.0, 'cogs' => 0.9, 'sat' => -6, 'hint' => 'Biaya turun, pembeli bisa kecewa'],
            'tetap' => ['label' => 'Harga dan porsi tetap', 'cost' => 0, 'demand' => 1.02, 'sat' => 2, 'hint' => 'Pembeli senang, untung menipis'],
        ]],
        6 => ['question' => 'Ulasan tentang warungmu menyebar di grup WhatsApp desa. Apa yang kamu lakukan?', 'options' => [
            'balas' => ['label' => 'Balas ulasan dan minta saran', 'cost' => 0, 'demand' => 1.03, 'sat' => 5, 'later' => [7 => 3], 'hint' => 'Pembeli merasa didengar'],
            'promo_ulang' => ['label' => 'Bagikan promo ke grup', 'cost' => 10000, 'demand' => 1.1, 'sat' => 1, 'hint' => 'Rp10.000, lebih banyak yang datang'],
            'abaikan' => ['label' => 'Abaikan', 'cost' => 0, 'demand' => 1.0, 'sat' => -2, 'hint' => 'Tidak ada biaya'],
        ]],
        7 => ['question' => 'Hari pasaran sekaligus awal liburan. Persiapan terakhir?', 'options' => [
            'pagi' => ['label' => 'Buka lebih pagi sampai malam', 'cost' => 10000, 'demand' => 1.15, 'sat' => 2, 'hint' => 'Rp10.000 listrik dan tenaga'],
            'titip' => ['label' => 'Titipkan sisa barang ke tetangga', 'cost' => 0, 'demand' => 1.0, 'sat' => 0, 'rescue' => 0.5, 'hint' => 'Separuh sisa terjual dengan harga modal'],
            'biasa' => ['label' => 'Buka seperti biasa', 'cost' => 0, 'demand' => 1.0, 'sat' => 0, 'hint' => 'Tanpa biaya tambahan'],
        ]],
    ];

    public const ENDINGS = [
        'ramai' => ['title' => 'Warung ramai, simbah bangga', 'body' => 'Tujuh hari terlewati dengan untung yang sehat dan pelanggan yang puas. Warung simbah siap menyambut musim liburan.'],
        'bertahan' => ['title' => 'Warung bertahan dengan catatan', 'body' => 'Warung masih buka, tapi ada keputusan yang membuat untung atau pelanggan berkurang. Pelajari catatan di bawah sebelum musim liburan.'],
        'belum' => ['title' => 'Warung belum berhasil', 'body' => 'Kali ini warung belum bisa hidup. Tidak apa-apa, ini tempat berlatih. Coba lagi dengan strategi yang berbeda.'],
    ];

    /** Jenis warung dari bidang minat profil (WB-03). */
    public static function kindFor(?array $interestSlugs): string
    {
        foreach ($interestSlugs ?? [] as $slug) {
            if (str_contains($slug, 'kuliner')) {
                return 'singkong';
            }
            if (str_contains($slug, 'kelontong')) {
                return 'kelontong';
            }
            if (str_contains($slug, 'oleh') || str_contains($slug, 'wisata') || str_contains($slug, 'kerajinan')) {
                return 'oleh_oleh';
            }
        }

        return 'singkong';
    }
}
