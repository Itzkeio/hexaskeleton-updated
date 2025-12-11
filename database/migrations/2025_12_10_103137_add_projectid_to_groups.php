<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
{
    Schema::table('groups', function (Blueprint $table) {
        $table->unsignedBigInteger('projectId')->nullable();

        $table->foreign('projectId')
            ->references('id')
            ->on('projects')
            ->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('groups', function (Blueprint $table) {
        $table->dropForeign(['projectId']);
        $table->dropColumn('projectId');
    });
}

};

