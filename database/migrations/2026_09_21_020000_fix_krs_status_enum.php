<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL ENUM mengikuti collation database. Karena itu 'Draft' dan 'draft'
        // dianggap duplikat dan tidak boleh berada bersamaan dalam ENUM.
        // Gunakan nilai sementara untuk memindahkan data lama terlebih dahulu.
        DB::statement("ALTER TABLE k_r_s MODIFY status ENUM(
            'Draft',
            'Diajukan',
            'Disetujui',
            'Ditolak',
            '__draft__',
            'submitted',
            'approved',
            'rejected',
            'published',
            'locked'
        ) NOT NULL DEFAULT 'Draft'");

        DB::statement("UPDATE k_r_s SET status = '__draft__' WHERE status = 'Draft'");

        // Sekarang nilai 'Draft' sudah tidak dipakai sehingga dapat diganti
        // dengan nilai kanonik 'draft'.
        DB::statement("ALTER TABLE k_r_s MODIFY status ENUM(
            '__draft__',
            'Diajukan',
            'Disetujui',
            'Ditolak',
            'submitted',
            'approved',
            'rejected',
            'published',
            'locked',
            'draft'
        ) NOT NULL DEFAULT 'draft'");

        DB::statement("UPDATE k_r_s SET status = CASE
            WHEN status = '__draft__' THEN 'draft'
            WHEN status = 'Diajukan' THEN 'submitted'
            WHEN status = 'Disetujui' THEN 'approved'
            WHEN status = 'Ditolak' THEN 'rejected'
            ELSE status
        END");

        // Batasi kembali ENUM hanya ke status kanonik.
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
        // Perluas sementara agar status kanonik dapat dikonversi kembali.
        DB::statement("ALTER TABLE k_r_s MODIFY status ENUM(
            'draft',
            'submitted',
            'approved',
            'rejected',
            'published',
            'locked',
            '__draft_old__',
            'Diajukan',
            'Disetujui',
            'Ditolak'
        ) NOT NULL DEFAULT 'draft'");

        DB::statement("UPDATE k_r_s SET status = CASE
            WHEN status = 'draft' THEN '__draft_old__'
            WHEN status = 'submitted' THEN 'Diajukan'
            WHEN status = 'approved' THEN 'Disetujui'
            WHEN status = 'rejected' THEN 'Ditolak'
            WHEN status = 'published' THEN 'Disetujui'
            WHEN status = 'locked' THEN 'Disetujui'
            ELSE status
        END");

        DB::statement("ALTER TABLE k_r_s MODIFY status ENUM(
            '__draft_old__',
            'Diajukan',
            'Disetujui',
            'Ditolak'
        ) NOT NULL DEFAULT '__draft_old__'");

        DB::statement("UPDATE k_r_s SET status = 'Diajukan' WHERE status = '__draft_old__'");

        DB::statement("ALTER TABLE k_r_s MODIFY status ENUM(
            'Draft',
            'Diajukan',
            'Disetujui',
            'Ditolak'
        ) NOT NULL DEFAULT 'Draft'");
    }
};
