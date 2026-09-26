<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->string('whatsapp_admin_website')->nullable()->after('school_phone');
            $table->string('whatsapp_admin_keuangan')->nullable()->after('whatsapp_admin_website');
            $table->string('whatsapp_admin_pmb')->nullable()->after('whatsapp_admin_keuangan');
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_admin_website',
                'whatsapp_admin_keuangan',
                'whatsapp_admin_pmb',
            ]);
        });
    }
};
