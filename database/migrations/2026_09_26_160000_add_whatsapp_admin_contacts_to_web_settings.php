<?php

use Illuminate\Database\\Migrations\\Migration;
use Illuminate\Database\\Schema\\Blueprint;
use Illuminate\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('web_settings')) {
            return;
        }

        // Idempotent: hosting database may already contain one or more
        // columns from an earlier/manual deployment.
        $columns = [
            'whatsapp_admin_website' => 'school_phone',
            'whatsapp_admin_keuangan' => 'whatsapp_admin_website',
            'whatsapp_admin_pmb' => 'whatsapp_admin_keuangan',
        ];

        foreach ($columns as $column => $after) {
            if (!Schema::hasColumn('web_settings', $column)) {
                Schema::table('web_settings', function (Blueprint $table) use ($column, $after) {
                    $table->string($column)->nullable()->after($after);
                });
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('web_settings')) {
            return;
        }

        $columns = [
            'whatsapp_admin_pmb',
            'whatsapp_admin_keuangan',
            'whatsapp_admin_website',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('web_settings', $column)) {
                Schema::table('web_settings', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
