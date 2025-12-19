<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // hapus dulu kolom lama
            $table->dropColumn('picId');
        });

        Schema::table('projects', function (Blueprint $table) {
            // buat ulang sebagai UUID
            $table->uuid('picId')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('picId');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->bigInteger('picId')->nullable();
        });
    }
};

