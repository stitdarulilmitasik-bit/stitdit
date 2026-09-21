<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('k_r_s') || !Schema::hasColumn('k_r_s', 'status')) {
            return;
        }

        DB::statement("ALTER TABLE k_r_s MODIFY status VARCHAR(30) NULL DEFAULT 'Draft'");

        // Konversi seluruh status lama ke satu standar bahasa Indonesia.
        DB::table('k_r_s')->whereIn('status', ['draft', 'Draft'])->update(['status' => 'Draft']);
        DB::table('k_r_s')->whereIn('status', ['submitted', 'Submitted', 'diajukan', 'Diajukan'])->update(['status' => 'Diajukan']);
        DB::table('k_r_s')->whereIn('status', ['approved', 'Approved', 'disetujui', 'Disetujui'])->update(['status' => 'Disetujui']);
        DB::table('k_r_s')->whereIn('status', ['rejected', 'Rejected', 'ditolak', 'Ditolak'])->update(['status' => 'Ditolak']);
        DB::table('k_r_s')->whereIn('status', ['locked', 'Locked', 'dikunci', 'Dikunci'])->update(['status' => 'Dikunci']);
        DB::table('k_r_s')->whereIn('status', ['published', 'Published', 'dicetak', 'Dicetak'])->update(['status' => 'Dicetak']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('k_r_s') || !Schema::hasColumn('k_r_s', 'status')) {
            return;
        }

        DB::table('k_r_s')->where('status', 'Draft')->update(['status' => 'draft']);
        DB::table('k_r_s')->where('status', 'Diajukan')->update(['status' => 'submitted']);
        DB::table('k_r_s')->where('status', 'Disetujui')->update(['status' => 'approved']);
        DB::table('k_r_s')->where('status', 'Ditolak')->update(['status' => 'rejected']);
        DB::table('k_r_s')->where('status', 'Dikunci')->update(['status' => 'locked']);
        DB::table('k_r_s')->where('status', 'Dicetak')->update(['status' => 'published']);
        DB::statement("ALTER TABLE k_r_s MODIFY status VARCHAR(30) NULL DEFAULT 'draft'");
    }
};
