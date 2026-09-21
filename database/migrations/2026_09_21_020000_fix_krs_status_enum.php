<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * KRS sebelumnya menggunakan ENUM status berbahasa Indonesia.
     * Status aplikasi sekarang menggunakan nilai kanonik berbahasa Inggris.
     */
    public function up(): void
    {
        // Pastikan seluruh nilai lama sudah dinormalisasi sebelum mempersempit/menata ENUM.
        DB::statement("UPDATE k_r_s SET status = CASE
            WHEN status = 'Draft' THEN 'draft'
            WHEN status = 'Diajukan' THEN 'submitted'
            WHEN status = 'Disetujui' THEN 'approved'
            WHEN status = 'Ditolak' THEN 'rejected'
            ELSE status
        END");

        DB::statement("ALTER TABLE k_r_s MODIFY status ENUM(
            'draft',
            'submitted',
            'approved',
            'rejected',
            'published',
            'locked'
        ) NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("UPDATE k_r_s SET status = CASE
            WHEN status = 'draft' THEN 'Draft'
            WHEN status = 'submitted' THEN 'Diajukan'
            WHEN status = 'approved' THEN 'Disetujui'
            WHEN status = 'rejected' THEN 'Ditolak'
            WHEN status = 'published' THEN 'Disetujui'
            WHEN status = 'locked' THEN 'Disetujui'
            ELSE status
        END");

        DB::statement("ALTER TABLE k_r_s MODIFY status ENUM(
            'Draft',
            'Diajukan',
            'Disetujui',
            'Ditolak'
        ) NOT NULL DEFAULT 'Draft'");
    }
};
