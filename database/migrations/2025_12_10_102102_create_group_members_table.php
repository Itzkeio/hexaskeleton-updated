<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->uuid('group_id');
            $table->uuid('user_id');

            // opsional: index untuk performa
            $table->index('group_id');
            $table->index('user_id');

            // foreign keys (opsional tapi bagus)
            // pastikan table groups & users pakai UUID
            $table->foreign('group_id')
                ->references('id')->on('groups')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_members');
    }
};
