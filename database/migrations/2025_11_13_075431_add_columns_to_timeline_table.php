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
        Schema::table('timeline', function (Blueprint $table) {
                if (Schema::hasColumn('timeline', 'date')) {
                $table->dropColumn('date');
            }
            
            // Tambah kolom baru
            $table->enum('type', ['plan', 'actual'])->default('plan')->after('projectId');
            $table->text('description')->nullable()->after('title');
            $table->string('color')->default('#fbbf24')->after('description');
            $table->integer('progress')->default(0)->after('color');
            
            // Tambah timestamps jika belum ada
            if (!Schema::hasColumn('timeline', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timeline', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'color', 'progress']);
            
            // Kembalikan kolom date jika perlu
            $table->date('date')->nullable();
        });
    }
};
