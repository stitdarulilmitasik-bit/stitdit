<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('web_settings') && !Schema::hasColumn('web_settings', 'whatsapp_admin_operator')) {
            Schema::table('web_settings', function (Blueprint $table) {
                $table->string('whatsapp_admin_operator')->nullable()->after('whatsapp_admin_pmb');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('web_settings') && Schema::hasColumn('web_settings', 'whatsapp_admin_operator')) {
            Schema::table('web_settings', function (Blueprint $table) {
                $table->dropColumn('whatsapp_admin_operator');
            });
        }
    }
};
