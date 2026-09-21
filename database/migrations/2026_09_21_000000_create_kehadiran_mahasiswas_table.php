<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadiran_mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('nilai_id')->constrained('nilais')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester');
            $table->unsignedTinyInteger('pertemuan');
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpa'])->default('Hadir');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['nilai_id', 'pertemuan']);
            $table->index(['semester', 'pertemuan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadiran_mahasiswas');
    }
};