<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->decimal('bobot_tugas', 5, 2)->default(20.00)->change();
            $table->decimal('bobot_quiz', 5, 2)->default(10.00)->change();
            $table->decimal('bobot_uts', 5, 2)->default(25.00)->change();
            $table->decimal('bobot_uas', 5, 2)->default(30.00)->change();
            $table->decimal('bobot_praktikum', 5, 2)->default(0.00)->change();
            $table->decimal('bobot_kehadiran', 5, 2)->default(15.00)->change();
        });
    }

    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->decimal('bobot_tugas', 5, 2)->default(20.00)->change();
            $table->decimal('bobot_quiz', 5, 2)->default(10.00)->change();
            $table->decimal('bobot_uts', 5, 2)->default(30.00)->change();
            $table->decimal('bobot_uas', 5, 2)->default(35.00)->change();
            $table->decimal('bobot_praktikum', 5, 2)->default(0.00)->change();
            $table->decimal('bobot_kehadiran', 5, 2)->default(5.00)->change();
        });
    }
};
