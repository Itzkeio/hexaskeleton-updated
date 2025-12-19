<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::create('kanban_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('userId'); // Siapa yang melakukan perubahan
            $table->uuid('kanbanId')->nullable(); // Task yang diubah
            $table->uuid('subtaskId')->nullable(); // Subtask yang diubah (jika ada)
            $table->unsignedBigInteger('projectId'); // Project reference
            $table->string('action', 50); // created, updated, deleted, status_changed, etc.
            $table->string('entity_type', 20); // 'task' atau 'subtask'
            $table->text('description'); // Deskripsi perubahan
            $table->json('old_values')->nullable(); // Data sebelum diubah
            $table->json('new_values')->nullable(); // Data setelah diubah
            $table->timestamps();

            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kanbanId')->references('id')->on('kanban')->onDelete('cascade');
            $table->foreign('subtaskId')->references('id')->on('subtask')->onDelete('cascade');
            $table->foreign('projectId')->references('id')->on('projects')->onDelete('cascade');
            
            $table->index(['projectId', 'created_at']);
            $table->index(['kanbanId', 'created_at']);
            $table->index(['userId', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kanban_logs');
    }
};
