<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Database production may already have this enum updated manually.
        // This migration keeps fresh and existing installations consistent.
        Schema::table('tahun_akademiks', function (Blueprint $table) {
            $table->enum('type', ['1', '2', '3', '4', '5', '6', '7', '8'])
                ->change();
        });
    }

    public function down(): void
    {
        // Do not automatically restore Ganjil/Genap because existing
        // semester 1-8 records cannot be safely converted without data loss.
    }
};
