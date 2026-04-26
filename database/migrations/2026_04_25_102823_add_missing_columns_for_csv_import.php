<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dictation_metrics', function (Blueprint $table) {
            if (!Schema::hasColumn('dictation_metrics', 'task_id')) {
                $table->uuid('task_id')->nullable();
                $table->index('task_id');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'realizado')) {
                $table->boolean('realizado')->default(false);
            }
            if (!Schema::hasColumn('tasks', 'realizado_em')) {
                $table->timestampTz('realizado_em')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'visto_profissional')) {
                $table->boolean('visto_profissional')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('dictation_metrics', function (Blueprint $table) {
            if (Schema::hasColumn('dictation_metrics', 'task_id')) {
                $table->dropIndex(['task_id']);
                $table->dropColumn('task_id');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            foreach (['realizado', 'realizado_em', 'visto_profissional'] as $col) {
                if (Schema::hasColumn('tasks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
