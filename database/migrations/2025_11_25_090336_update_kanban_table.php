<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kanban', function (Blueprint $table) {
            // Ubah status jadi UUID reference ke kanban_status
            $table->text('status')->nullable()->change();
            
            // Tambah notes field
            $table->text('notes')->nullable()->after('description');
            
            // Tambah soft delete flag
            $table->boolean('flag_delete')->default(false)->after('duration');
            $table->timestamp('deleted_at')->nullable()->after('flag_delete');
            $table->uuid('deleted_by')->nullable()->after('deleted_at');
            
            // Foreign key untuk deleted_by (asumsikan ada tabel users)
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('kanban', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['notes', 'flag_delete', 'deleted_at', 'deleted_by']);
        });
    }
};