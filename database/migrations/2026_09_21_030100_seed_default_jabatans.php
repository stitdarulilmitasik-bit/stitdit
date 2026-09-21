<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['Ketua STIT', 'Pimpinan', 10],
            ['Pembantu Ketua I', 'Pimpinan', 20],
            ['Pembantu Ketua II', 'Pimpinan', 30],
            ['Pembantu Ketua III', 'Pimpinan', 40],
            ['Ketua Program Studi', 'Program Studi', 50],
            ['Dosen Pembimbing Akademik', 'Akademik', 60],
            ['Sekretaris Program Studi', 'Program Studi', 70],
            ['Kepala LPM', 'Akademik', 80],
            ['Kepala LPPM', 'Akademik', 90],
            ['Kepala Bagian Administrasi', 'Administrasi', 100],
            ['Bendahara', 'Administrasi', 110],
        ];

        foreach ($defaults as [$name, $category, $sortOrder]) {
            $exists = DB::table('jabatans')->where('name', $name)->exists();

            if (!$exists) {
                DB::table('jabatans')->insert([
                    'code' => 'JBT-' . Str::upper(Str::random(8)),
                    'name' => $name,
                    'category' => $category,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('jabatans')
            ->whereIn('name', [
                'Ketua STIT',
                'Pembantu Ketua I',
                'Pembantu Ketua II',
                'Pembantu Ketua III',
                'Ketua Program Studi',
                'Dosen Pembimbing Akademik',
                'Sekretaris Program Studi',
                'Kepala LPM',
                'Kepala LPPM',
                'Kepala Bagian Administrasi',
                'Bendahara',
            ])
            ->whereNull('dosen_id')
            ->delete();
    }
};
