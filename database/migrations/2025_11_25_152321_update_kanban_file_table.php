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
         Schema::table('kanban_files', function (Blueprint $table) {
        $table->boolean('flag_delete')->default(false)->after('file_path');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanban_files', function (Blueprint $table) {
        $table->dropColumn('flag_delete');
    });
    }
};
