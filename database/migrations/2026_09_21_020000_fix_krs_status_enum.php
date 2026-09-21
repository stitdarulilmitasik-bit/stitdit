<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Perluas ENUM terlebih dahulu agar nilai kanonik dapat ditulis.
        DB::statement("ALTER TABLE k_r_s MODIFY status ENUM(
            'Draft',
            'Diajukan',
            'Disetujui',
            'Ditolak',
            'draft',
            'submitted',
            'approved',
            'rejected',
            'published',
            'locked'
        ) NOT NULL DEFAULT 'draft'");

        // Konversi data lama ke status kanonik.
        DB::statement("UPDATE k_r_s SET status = CASE
            WHEN status = 'Draft' THEN 'draft'
            WHEN status = 'Diajukan' THEN 'submitted'
            WHEN status = 'Disetujui' THEN 'approved'
            WHEN status = 'Ditolak' THEN 'rejected'
            ELSE status
        END");

        // Setelah data dinormalisasi, batasi ENUM ke nilai kanonik.
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
        DB::statement("ALTER TABLE k_r_s MODIFY status ENUM(
            'Draft',
            'Diajukan',
            'Disetujui',
            'Ditolak',
            'draft',
            'submitted',
            'approved',
            'rejected',
            'published',
            'locked'
        ) NOT NULL DEFAULT 'Draft'");

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
