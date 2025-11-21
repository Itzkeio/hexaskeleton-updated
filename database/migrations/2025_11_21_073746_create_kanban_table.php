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
        Schema::create('kanban', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->unsignedBigInteger('projectId');
        $table->string('title');
        $table->date('date_start')->nullable();
        $table->date('date_end')->nullable();
        $table->integer('duration')->nullable();
        $table->uuid('picId')->nullable();
        $table->text('description')->nullable();
        $table->string('priority')->default('low');
        $table->string('status')->default('todo'); // todo, inprogress, finished
        $table->timestamps();

        $table->foreign('projectId')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban');
    }
};
