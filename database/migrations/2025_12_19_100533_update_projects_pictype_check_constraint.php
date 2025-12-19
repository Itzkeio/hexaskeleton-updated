<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
     public function up(): void
    {
        DB::statement('
            ALTER TABLE projects
            DROP CONSTRAINT IF EXISTS projects_picType_check
        ');

        DB::statement('
            ALTER TABLE projects
            ADD CONSTRAINT projects_picType_check
            CHECK ("picType" IN (\'individual\', \'department\'))
        ');
    }

    public function down(): void
    {
        DB::statement('
            ALTER TABLE projects
            DROP CONSTRAINT IF EXISTS projects_picType_check
        ');

        DB::statement('
            ALTER TABLE projects
            ADD CONSTRAINT projects_picType_check
            CHECK ("picType" IN (\'user\', \'department\'))
        ');
    }
};
