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
       // Drop id kalau salah tipe
    Schema::table('kanban_statuses', function (Blueprint $table) {
        $table->dropColumn('id');
    });

    // Tambah ulang auto-increment ID
    Schema::table('kanban_statuses', function (Blueprint $table) {
        $table->id()->first();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanban_statuses', function (Blueprint $table) {
            //
        });
    }
};
