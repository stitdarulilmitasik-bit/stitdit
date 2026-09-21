<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('krs_details', 'jadwal_kuliah_id')) {
            Schema::table('krs_details', function (Blueprint $table) {
                $table->foreignId('jadwal_kuliah_id')
                    ->nullable()
                    ->after('kelas_id')
                    ->constrained('jadwal_kuliahs')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('krs_details', 'jadwal_kuliah_id')) {
            Schema::table('krs_details', function (Blueprint $table) {
                $table->dropForeign(['jadwal_kuliah_id']);
                $table->dropColumn('jadwal_kuliah_id');
            });
        }
    }
};
