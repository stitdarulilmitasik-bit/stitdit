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

        DB::table('k_r_s')->whereIn('status', ['Draft', 'draft'])->update(['status' => 'draft']);
        DB::table('k_r_s')->whereIn('status', ['Diajukan', 'diajukan', 'submitted'])->update(['status' => 'submitted']);
        DB::table('k_r_s')->whereIn('status', ['Disetujui', 'disetujui', 'approved'])->update(['status' => 'approved']);
        DB::table('k_r_s')->whereIn('status', ['Ditolak', 'ditolak', 'rejected'])->update(['status' => 'rejected']);
        DB::table('k_r_s')->whereIn('status', ['Dipublish', 'dipublish', 'published'])->update(['status' => 'published']);
        DB::table('k_r_s')->whereIn('status', ['Dikunci', 'dikunci', 'locked'])->update(['status' => 'locked']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('k_r_s') || !Schema::hasColumn('k_r_s', 'status')) {
            return;
        }

        DB::table('k_r_s')->where('status', 'draft')->update(['status' => 'Draft']);
        DB::table('k_r_s')->where('status', 'submitted')->update(['status' => 'Diajukan']);
        DB::table('k_r_s')->where('status', 'approved')->update(['status' => 'Disetujui']);
        DB::table('k_r_s')->where('status', 'rejected')->update(['status' => 'Ditolak']);
        DB::table('k_r_s')->where('status', 'published')->update(['status' => 'Dipublish']);
        DB::table('k_r_s')->where('status', 'locked')->update(['status' => 'Dikunci']);
    }
};