<?php

namespace Database\Seeders;

use App\Models\ShopEvent;
use Illuminate\Database\Seeder;

/** Bank kejadian cadangan bila AI tidak tersedia (WB-05). */
class ShopEventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [1, null, 'Tetangga mampir mencicipi', 'Beberapa tetangga mampir karena penasaran warung simbah buka lagi. Mereka belum tentu belanja banyak.', 0.9, 1, 0],
            [1, null, 'Jalan masih sepi', 'Pagi ini jalan menuju pantai masih lengang. Hanya warga sekitar yang lewat.', 0.86, 1, 0],
            [1, 'singkong', 'Pesanan arisan kecil', 'Ibu-ibu arisan menanyakan apakah ada tiwul untuk sore nanti.', 0.98, 1, 0],
            [1, 'oleh_oleh', 'Wisatawan nyasar', 'Satu mobil wisatawan berhenti menanyakan arah ke pantai, lalu melihat-lihat dagangan.', 0.95, 1, 0],
            [1, 'kelontong', 'Warga belanja harian', 'Warga sekitar mulai belanja kebutuhan harian di warungmu.', 1.0, 1, 0],
            [2, null, 'Hujan deras tanpa jeda', 'Hujan turun dari subuh sampai sore. Jalan becek dan sedikit orang keluar rumah.', 0.5, 1, -5000],
            [2, null, 'Atap bocor sedikit', 'Air menetes di pojok warung. Kamu harus mengeluarkan uang untuk menambal sementara.', 0.6, 1.02, -10000],
            [2, null, 'Gerimis sepanjang hari', 'Gerimis tipis membuat orang malas mampir, tapi tukang ojek berteduh dan jajan.', 0.62, 1, 0],
            [2, 'singkong', 'Gorengan cepat lembek', 'Udara lembap membuat keripik yang terbuka cepat melempem.', 0.55, 1.03, 0],
            [2, 'oleh_oleh', 'Wisatawan batal ke pantai', 'Banyak rombongan membatalkan rencana ke pantai karena ombak tinggi.', 0.47, 1, 0],
            [3, null, 'Warung baru di seberang', 'Warung baru di seberang membagikan brosur harga murah.', 0.75, 1, 0],
            [3, null, 'Pesaing pasang musik keras', 'Warung seberang memutar musik keras. Sebagian pembeli tertarik, sebagian terganggu.', 0.8, 1, 0],
            [3, null, 'Pembeli membandingkan harga', 'Beberapa pembeli terang-terangan membandingkan harga dengan warung seberang.', 0.72, 1, 0],
            [3, 'singkong', 'Pesaing jual gorengan', 'Warung seberang menjual gorengan hangat dengan harga lebih murah.', 0.74, 1, 0],
            [3, 'oleh_oleh', 'Kios suvenir baru', 'Kios suvenir baru buka dengan banyak pilihan kaos.', 0.78, 1, 0],
            [4, null, 'Dua bus wisata berhenti', 'Dua bus wisata dari Jawa Tengah berhenti di depan warung untuk istirahat.', 1.8, 1, 10000],
            [4, null, 'Rombongan sekolah', 'Rombongan karyawisata sekolah turun untuk membeli bekal.', 1.7, 1, 0],
            [4, null, 'Bus pariwisata mogok sebentar', 'Satu bus mogok di depan warung, penumpangnya menunggu hampir satu jam.', 1.65, 1, 5000],
            [4, 'oleh_oleh', 'Rombongan pencari oleh-oleh', 'Pemandu wisata mengajak rombongan mencari oleh-oleh khas Gunungkidul.', 1.9, 1, 15000],
            [4, 'kelontong', 'Sopir bus belanja minuman', 'Sopir dan kernet membeli air dan mi untuk perjalanan pulang.', 1.6, 1, 0],
            [5, null, 'Harga bahan di pasar naik', 'Pedagang pasar bilang harga bahan naik karena pasokan berkurang.', 0.95, 1.25, 0],
            [5, null, 'Ongkos angkut naik', 'Harga bensin naik, ongkos angkut barang ke warung ikut naik.', 0.97, 1.2, 0],
            [5, 'singkong', 'Singkong langka', 'Kemarau membuat singkong lebih sedikit dan harganya naik.', 0.92, 1.3, 0],
            [5, 'kelontong', 'Agen menaikkan harga grosir', 'Agen grosir menaikkan harga beberapa barang kebutuhan.', 0.95, 1.22, 0],
            [5, 'oleh_oleh', 'Perajin menaikkan harga', 'Perajin topeng menaikkan harga karena bahan kayu naik.', 0.96, 1.28, 0],
            [6, null, 'Ulasan di grup desa', 'Seseorang menulis tentang warungmu di grup WhatsApp desa.', 1.0, 1, 0],
            [6, null, 'Foto warung dibagikan', 'Wisatawan membagikan foto warungmu di media sosial.', 1.03, 1, 5000],
            [6, null, 'Keluhan kecil tersebar', 'Ada yang mengeluh pelayanan lambat kemarin, kabarnya cepat menyebar.', 0.96, 1, 0],
            [6, null, 'Pelanggan setia datang lagi', 'Pembeli kemarin datang lagi membawa teman.', 1.04, 1, 0],
            [7, null, 'Pasar Legi dan libur sekolah', 'Hari pasaran bertepatan dengan awal libur sekolah. Jalan ramai sejak pagi.', 1.6, 1, 10000],
            [7, null, 'Festival kecil di kapanewon', 'Ada festival kecil di lapangan dekat warung. Orang lalu lalang sampai malam.', 1.5, 1, 15000],
            [7, null, 'Arus mudik wisata', 'Kendaraan wisatawan memadati jalan menuju pantai.', 1.7, 1, 20000],
            [7, 'singkong', 'Pesanan tiwul untuk kenduri', 'Tetangga memesan tiwul untuk kenduri sore hari.', 1.55, 1, 20000],
            [7, 'oleh_oleh', 'Pemandu wisata jadi langganan', 'Pemandu wisata mulai rutin membawa rombongan ke warungmu.', 1.65, 1, 10000],
        ];

        foreach ($events as [$day, $kind, $title, $story, $demand, $cost, $cash]) {
            ShopEvent::query()->updateOrCreate(['day' => $day, 'title' => $title], ['kind' => $kind, 'story' => $story, 'demand' => $demand, 'cost' => $cost, 'cash' => $cash]);
        }
    }
}
