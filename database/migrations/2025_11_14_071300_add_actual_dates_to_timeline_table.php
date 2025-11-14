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
             $table->string('title')->nullable(); // Bisa diisi milestone, sprint name, dsb
            $table->text('description')->nullable();

            // Actual start & end date (warna hijau di chart)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Opsional: progress aktual (0–100%)
            $table->Integer('progress')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timeline', function (Blueprint $table) {
             $table->string('title')->nullable(); // Bisa diisi milestone, sprint name, dsb
            $table->text('description')->nullable();

            // Actual start & end date (warna hijau di chart)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Opsional: progress aktual (0–100%)
            $table->Integer('progress')->default(0);

            $table->timestamps();
        });
    }
};
