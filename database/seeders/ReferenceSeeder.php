<?php

namespace Database\Seeders;

use App\Models\BusinessField;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            'Wonosari' => 'Pusat kabupaten, pasar besar, kuliner dan jasa',
            'Nglipar' => 'Pertanian lahan kering, kayu, wisata alam',
            'Playen' => 'Kerajinan kayu dan topeng, singkong, wisata air terjun',
            'Patuk' => 'Batik dan kerajinan, wisata Gunung Api Purba, kebun buah',
            'Paliyan' => 'Pertanian tadah hujan, ternak, hutan',
            'Panggang' => 'Pantai, perikanan, ternak kambing',
            'Tepus' => 'Pantai wisata, homestay, olahan rumput laut',
            'Semanu' => 'Wisata goa, pertanian, olahan singkong',
            'Karangmojo' => 'Wisata goa dan air, pertanian, kerajinan',
            'Ponjong' => 'Singkong dan olahannya, wisata alam, peternakan',
            'Rongkop' => 'Pertanian lahan kering, ternak, pantai',
            'Semin' => 'Pertanian padi, kerajinan bambu, jalur ke Wonogiri',
            'Ngawen' => 'Pertanian, wisata sejarah, jalur ke Klaten',
            'Gedangsari' => 'Batik tulis, kerajinan, kebun',
            'Saptosari' => 'Pantai, perikanan, olahan hasil laut',
            'Girisubo' => 'Pantai dan pelabuhan ikan, ternak',
            'Tanjungsari' => 'Pantai wisata ramai, kuliner laut, homestay',
            'Purwosari' => 'Pantai, wisata paralayang, pertanian',
        ];

        foreach ($districts as $name => $potential) {
            District::query()->updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'potential' => $potential]);
        }

        // Disusun mengikuti 13 bidang usaha dan 5 bidang pendukung IPI. Nama perlu dicocokkan dengan Company Profile IPI 2026.
        $fields = [
            'usaha' => [
                ['Kuliner dan makanan olahan', 'makanan kuliner keripik singkong olahan jajan kue minuman warung makan'],
                ['Pertanian dan perkebunan', 'tani pertanian kebun sayur buah bibit pupuk'],
                ['Peternakan dan perikanan', 'ternak kambing sapi ayam ikan lele budidaya'],
                ['Kerajinan tangan', 'kerajinan kayu bambu topeng anyaman souvenir'],
                ['Fesyen dan batik', 'batik baju jahit kain fesyen pakaian'],
                ['Pariwisata dan penginapan', 'wisata pantai homestay penginapan pemandu goa'],
                ['Perdagangan dan kelontong', 'kelontong toko warung dagang sembako grosir'],
                ['Oleh-oleh dan suvenir', 'oleh-oleh suvenir wisatawan pantai cendera mata'],
                ['Jasa dan perbaikan', 'jasa bengkel servis reparasi cuci laundry'],
                ['Kecantikan dan kesehatan', 'salon kecantikan jamu herbal pijat'],
                ['Pendidikan dan pelatihan', 'les kursus bimbel pelatihan'],
                ['Kreatif dan digital', 'desain foto video konten digital media sosial'],
                ['Transportasi dan logistik', 'ojek antar kirim angkut sewa kendaraan'],
            ],
            'pendukung' => [
                ['Keuangan dan pembukuan', 'keuangan pembukuan kas modal hpp harga'],
                ['Pemasaran dan penjualan', 'pemasaran jualan promosi pelanggan branding'],
                ['Legalitas dan perizinan', 'izin nib halal pirt legal'],
                ['Kepemimpinan dan tim', 'karyawan tim kepemimpinan'],
                ['Teknologi dan digitalisasi', 'aplikasi online digital internet'],
            ],
        ];

        foreach ($fields as $kind => $items) {
            foreach ($items as $sort => [$name, $keywords]) {
                BusinessField::query()->updateOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name, 'kind' => $kind, 'keywords' => $keywords, 'sort' => $sort + 1],
                );
            }
        }
    }
}
