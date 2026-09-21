<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalisasi data nilai lama yang masih menggunakan bobot kehadiran 20%.
     * Hanya baris dengan total bobot tepat 105% yang diubah, sehingga bobot
     * khusus yang sudah diset akademik tidak ikut tertimpa.
     */
    public function up(): void
    {
        DB::table('nilais')
            ->where('bobot_kehadiran', 20)
            ->whereRaw('(COALESCE(bobot_tugas,0) + COALESCE(bobot_quiz,0) + COALESCE(bobot_uts,0) + COALESCE(bobot_uas,0) + COALESCE(bobot_praktikum,0) + COALESCE(bobot_kehadiran,0)) = 105')
            ->update(['bobot_kehadiran' => 15]);
    }

    public function down(): void
    {
        DB::table('nilais')
            ->where('bobot_kehadiran', 15)
            ->whereRaw('(COALESCE(bobot_tugas,0) + COALESCE(bobot_quiz,0) + COALESCE(bobot_uts,0) + COALESCE(bobot_uas,0) + COALESCE(bobot_praktikum,0) + COALESCE(bobot_kehadiran,0)) = 100')
            ->update(['bobot_kehadiran' => 20]);
    }
};
