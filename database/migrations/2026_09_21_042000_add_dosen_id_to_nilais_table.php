<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('nilais', 'dosen_id')) {
            Schema::table('nilais', function (Blueprint $table) {
                $table->unsignedBigInteger('dosen_id')->nullable()->after('matkul_id');
                $table->index('dosen_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nilais', 'dosen_id')) {
            Schema::table('nilais', function (Blueprint $table) {
                $table->dropIndex(['dosen_id']);
                $table->dropColumn('dosen_id');
            });
        }
    }
};
