<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legalisir_pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('nomor_pengajuan', 50)->unique();
            $table->string('jenis_dokumen', 150);
            $table->unsignedInteger('jumlah')->default(1);
            $table->text('keperluan');
            $table->text('catatan_mahasiswa')->nullable();
            $table->string('file_pendukung')->nullable();
            $table->date('tanggal_pengajuan');
            $table->string('status', 30)->default('Diajukan');
            $table->text('catatan_admin')->nullable();
            $table->string('nomor_legalisir', 100)->nullable();
            $table->date('tanggal_legalisir')->nullable();
            $table->string('pejabat_nama', 150)->nullable();
            $table->string('pejabat_jabatan', 150)->nullable();
            $table->string('file_hasil')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['mahasiswa_id', 'status']);
            $table->index(['tanggal_pengajuan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legalisir_pengajuans');
    }
};
