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
        Schema::create('kanban_status', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('projectId');
            $table->string('name', 100); // e.g., "To Do", "In Review", "Done"
            $table->string('color', 7)->default('#6c757d'); // Hex color code
            $table->integer('order')->default(0); // Order untuk sorting
            $table->boolean('is_default')->default(false); // Status default saat create task
            $table->timestamps();

            $table->foreign('projectId')->references('id')->on('projects')->onDelete('cascade');
            $table->index(['projectId', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_status');
    }
};
