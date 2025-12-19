<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('timeline', function (Blueprint $table) {
            $table->foreignId('projectId')
                  ->constrained('projects')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('timeline', function (Blueprint $table) {
            $table->dropForeign(['projectId']);
            $table->dropColumn('projectId');
        });
    }
};

