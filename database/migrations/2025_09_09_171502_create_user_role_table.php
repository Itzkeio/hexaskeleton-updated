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
        Schema::create('userRoles', function (Blueprint $table) {
            $table->id();
            $table->uuid('userId');
            $table->uuid('roleId');
            $table->date('createdAt')->default(now());
            $table->date('updatedAt')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userRoles');
    }
};
