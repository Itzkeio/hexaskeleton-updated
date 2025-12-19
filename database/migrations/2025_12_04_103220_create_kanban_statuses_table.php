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
        Schema::create('kanban_statuses', function (Blueprint $table) {
        $table->uuid('id')->primary();

    $table->unsignedBigInteger('projectId');
    $table->foreign('projectId')
          ->references('id')
          ->on('projects')
          ->cascadeOnDelete();

    $table->string('key')->unique();
    $table->string('label');
    $table->string('color_bg')->default('#f1f1f1');
    $table->string('color_border')->default('#ccc');
    $table->integer('order')->default(0);
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_statuses');
    }
};
