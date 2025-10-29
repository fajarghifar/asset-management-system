<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\Area;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil area berdasarkan kode yang ada di AreaSeeder Anda
        $jmp1 = Area::where('code', 'OFF-JMP1')->first();
        $jmp2 = Area::where('code', 'OFF-JMP2')->first();
        $btStore = Area::where('code', 'STORE-BT')->first();

        // Validasi: pastikan semua area ditemukan
        if (!$jmp1 || !$jmp2 || !$btStore) {
            $this->command->error('Area tidak ditemukan! Pastikan AreaSeeder sudah dijalankan.');
            return;
        }

        $locations = [
            // === Lokasi di JMP 1 (Kantor) ===
            [
                'code' => 'JMP1-RM1',
                'name' => 'Ruang Meeting 1',
                'area_id' => $jmp1->id,
                'is_borrowable' => true,
                'description' => 'Ruang meeting kecil di lantai 1',
            ],
            [
                'code' => 'JMP1-RM2',
                'name' => 'Ruang Meeting Besar',
                'area_id' => $jmp1->id,
                'is_borrowable' => true,
                'description' => 'Ruang meeting utama dengan proyektor',
            ],
            [
                'code' => 'JMP1-SRV',
                'name' => 'Ruang Server',
                'area_id' => $jmp1->id,
                'is_borrowable' => false,
                'description' => 'Akses terbatas, hanya IT',
            ],
            [
                'code' => 'JMP1-WH1',
                'name' => 'Gudang Barang Jadi 1',
                'area_id' => $jmp1->id,
                'is_borrowable' => false,
                'description' => 'Penyimpanan produk jadi',
            ],

            // === Lokasi di JMP 2 (Kantor) ===
            [
                'code' => 'JMP2-RM1',
                'name' => 'Ruang Rapat Produksi',
                'area_id' => $jmp2->id,
                'is_borrowable' => true,
                'description' => 'Untuk koordinasi tim produksi',
            ],
            [
                'code' => 'JMP2-LAB',
                'name' => 'Laboratorium Desain',
                'area_id' => $jmp2->id,
                'is_borrowable' => false,
                'description' => 'Area eksklusif desainer batik',
            ],

            // === Lokasi di BT Batik Trusmi (Store) ===
            [
                'code' => 'BT-SHOP1',
                'name' => 'Area Display Utama',
                'area_id' => $btStore->id,
                'is_borrowable' => false,
                'description' => 'Area pameran dan penjualan',
            ],
            [
                'code' => 'BT-EVT',
                'name' => 'Ruang Event & Workshop',
                'area_id' => $btStore->id,
                'is_borrowable' => true,
                'description' => 'Untuk workshop batik atau acara khusus',
            ],
            [
                'code' => 'BT-WH',
                'name' => 'Gudang Retail',
                'area_id' => $btStore->id,
                'is_borrowable' => false,
                'description' => 'Stok barang retail',
            ],
        ];

        foreach ($locations as $data) {
            Location::create($data);
        }

        $this->command->info('✅ Lokasi berhasil di-seed untuk JMP 1, JMP 2, dan BT Batik Trusmi.');
    }
}
