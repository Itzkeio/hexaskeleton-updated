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
        Schema::create('kanban_files', function (Blueprint $table) {
          $table->uuid('id')->primary();
            $table->uuid('kanbanId')->nullable(); // Task file
            $table->uuid('subtaskId')->nullable(); // Subtask file
            $table->uuid('uploadedBy'); // User yang upload
            $table->string('filename'); // Original filename
            $table->string('file_path'); // Path di storage
            $table->string('file_type', 50)->nullable(); // mime type
            $table->bigInteger('file_size')->nullable(); // dalam bytes
            $table->text('description')->nullable(); // Deskripsi file
            $table->timestamps();

            $table->foreign('kanbanId')->references('id')->on('kanban')->onDelete('cascade');
            $table->foreign('subtaskId')->references('id')->on('subtask')->onDelete('cascade');
            $table->foreign('uploadedBy')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['kanbanId', 'created_at']);
            $table->index(['subtaskId', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_files');
    }
};
