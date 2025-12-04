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
        // Tambahkan deleted_at ke tabel kanban jika belum ada
        if (!Schema::hasColumn('kanban', 'deleted_at')) {
            Schema::table('kanban', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Tambahkan deleted_at ke tabel subtasks jika belum ada
        if (!Schema::hasColumn('subtask', 'deleted_at')) {
            Schema::table('subtask', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Tambahkan deleted_at ke tabel kanban_files jika belum ada
        if (!Schema::hasColumn('kanban_files', 'deleted_at')) {
            Schema::table('kanban_files', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanban', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('subtask', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('kanban_files', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};