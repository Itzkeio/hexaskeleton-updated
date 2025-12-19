<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timeline', function (Blueprint $table) {
            $table->dropColumn([ 'description', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('timeline', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('date')->nullable();
        });
    }
};
