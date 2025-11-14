<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; 

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable extension (optional if already enabled)
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('compCode', 50);
            $table->string('compName', 255);
            $table->string('divCode', 255);
            $table->string('divName', 255);
            $table->string('nik', 50)->unique();
            $table->string('name', 255);
            $table->string('userPrincipalName', 255)->unique();
            $table->string('email', 255)->unique();
            $table->string('empTypeGroup', 255)->nullable();
            $table->string('jobLvlName', 255)->nullable();
            $table->string('jobTtlName', 255)->nullable();
            $table->string('deptName', 255)->nullable();
            $table->date('createdAt')->default(now());
            $table->date('updatedAt')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
