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
        Schema::create('logs', function (Blueprint $table) {
            $table->id(); // auto-increment INT PRIMARY KEY
            $table->string('compCode', 50);
            $table->string('compName', 255);
            $table->string('username', 255);
            $table->string('activity', 255);
            $table->string('description', 255);
            $table->timestamp('createdAt')->useCurrent(); // DATETIME DEFAULT GETDATE()
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
