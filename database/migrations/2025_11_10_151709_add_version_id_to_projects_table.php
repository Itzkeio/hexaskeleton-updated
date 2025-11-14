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
        Schema::table('projects', function (Blueprint $table) {
            $table->bigInteger('versionId')->nullable()->after('icon');

            // Foreign key constraint
            $table->foreign('versionId')
                ->references('id')
                ->on('versions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
             // Hapus foreign key dulu sebelum drop column
            $table->dropForeign(['versionId']);
            $table->dropColumn('versionId');
        });
    }
};
