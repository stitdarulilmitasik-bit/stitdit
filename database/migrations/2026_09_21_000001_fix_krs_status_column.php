<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('k_r_s') || !Schema::hasColumn('k_r_s', 'status')) {
            return;
        }

        // KRS menggunakan status workflow: draft, submitted, approved,
        // rejected, locked, dan published. Gunakan VARCHAR agar MySQL
        // tidak menolak status baru dengan "Data truncated".
        DB::statement("ALTER TABLE k_r_s MODIFY status VARCHAR(30) NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('k_r_s') || !Schema::hasColumn('k_r_s', 'status')) {
            return;
        }

        // Pertahankan nilai status yang dipakai aplikasi dan kembalikan
        // bentuk enum hanya untuk rollback.
        DB::statement("ALTER TABLE k_r_s MODIFY status ENUM('draft','submitted','approved','rejected','locked','published') NULL DEFAULT 'draft'");
    }
};
