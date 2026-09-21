<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('published_at');
            $table->timestamp('approved_at')->nullable()->after('submitted_at');
            $table->timestamp('locked_at')->nullable()->after('approved_at');
            $table->unsignedBigInteger('submitted_by')->nullable()->after('deleted_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('submitted_by');
            $table->unsignedBigInteger('locked_by')->nullable()->after('approved_by');
            $table->text('workflow_note')->nullable()->after('locked_by');
            $table->unsignedInteger('grade_version')->default(1)->after('workflow_note');
        });

        // Keep the existing enum values and add explicit workflow stages.
        DB::statement("ALTER TABLE nilais MODIFY status ENUM('Draft','Submitted','Approved','Published','Locked') NOT NULL DEFAULT 'Draft'");

        Schema::create('nilai_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nilai_id');
            $table->string('action', 40);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->unsignedBigInteger('actor_dosen_id')->nullable();
            $table->text('reason')->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->timestamps();

            $table->foreign('nilai_id')->references('id')->on('nilais')->onDelete('cascade');
            $table->index(['nilai_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_audits');

        Schema::table('nilais', function (Blueprint $table) {
            $table->dropColumn([
                'submitted_at',
                'approved_at',
                'locked_at',
                'submitted_by',
                'approved_by',
                'locked_by',
                'workflow_note',
                'grade_version',
            ]);
        });

        DB::statement("ALTER TABLE nilais MODIFY status ENUM('Draft','Published','Locked') NOT NULL DEFAULT 'Draft'");
    }
};
