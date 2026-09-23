<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep existing installations consistent with semester values 1-8.
        Schema::table('tahun_akademiks', function (Blueprint $table) {
            $table->enum('type', ['1', '2', '3', '4', '5', '6', '7', '8'])
                ->change();
        });
    }

    public function down(): void
    {
        // Keep semester 1-8 values on rollback to avoid data loss.
    }
};
