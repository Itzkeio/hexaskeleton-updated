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
        Schema::table('userRoles', function (Blueprint $table) {
            $table->foreign('userId', 'fk_user_roles_user')
                  ->references('id')
                  ->on('users');

            $table->foreign('roleId', 'fk_user_roles_role')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('userRoles', function (Blueprint $table) {
            $table->dropForeign('fk_user_roles_user');
            $table->dropForeign('fk_user_roles_role');
        });
    }
};
