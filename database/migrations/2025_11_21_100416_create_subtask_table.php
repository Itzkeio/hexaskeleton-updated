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
        Schema::create('subtask', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('kanbanId');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['todo', 'inprogress', 'finished'])->default('todo');
            $table->timestamps();

            $table->foreign('kanbanId')->references('id')->on('kanban')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subtask');
    }
};
