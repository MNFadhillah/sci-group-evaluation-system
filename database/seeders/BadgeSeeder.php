<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Badge::truncate();
        Schema::enableForeignKeyConstraints();

        $badges = [
            // ==========================================
            // 🏅 BADGE KELOMPOK (Berdasarkan SCI) - ID 1, 2, 3
            // ==========================================
            [
                'id' => 4,
                'name' => 'The Carry',
                'description' => 'Kontribusi berada di atas proporsi; membantu menyelesaikan bagian anggota lain; memberikan usaha lebih besar (Khusus Kelompok).',
                'path_icon' => 'img/the_carry.png',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Solid Partner',
                'description' => 'Memberikan kontribusi yang relatif seimbang; menjalankan tanggung jawab; anggota yang stabil (Khusus Kelompok).',
                'path_icon' => 'img/solid_partner.png',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Need Help',
                'description' => 'Kontribusi berada di bawah proporsi; membutuhkan dukungan atau peningkatan (Khusus Kelompok).',
                'path_icon' => 'img/need_help.png',
                'created_at' => now(), 'updated_at' => now(),
            ],

            // ==========================================
            // 🏅 BADGE MANDIRI (Berdasarkan Kecepatan/Nilai) - ID 4, 5, 6
            // ==========================================
            [
                'id' => 1,
                'name' => 'Fastest',
                'description' => 'Menyelesaikan aktivitas tugas/kuis mandiri dengan waktu paling cepat di kelas.',
                'path_icon' => 'img/1.png', // Sesuaikan path icon lama kamu
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Top 3',
                'description' => 'Berhasil meraih peringkat 3 nilai tertinggi pada aktivitas mandiri.',
                'path_icon' => 'img/2.png',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Smartest',
                'description' => 'Mendapatkan nilai sempurna atau berhasil melampaui KKM pada aktivitas mandiri.',
                'path_icon' => 'img/3.png',
                'created_at' => now(), 'updated_at' => now(),
            ],
        ];

        Badge::insert($badges);
        $this->command->info('Berhasil! 6 Badge (3 Kelompok & 3 Mandiri) telah dimasukkan ke database.');
    }
}