<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE [egresos].[importaciones]
            DROP CONSTRAINT [ck_egr_import_estado]
        SQL);
        DB::statement(<<<'SQL'
            ALTER TABLE [egresos].[importaciones]
            ADD CONSTRAINT [ck_egr_import_estado]
            CHECK ([estado] IN (N'pending', N'running', N'completed', N'failed', N'cancelled', N'rolled_back'))
        SQL);
    }

    public function down(): void
    {
        DB::table('egresos.importaciones')
            ->where('estado', 'cancelled')
            ->update(['estado' => 'failed']);

        DB::statement(<<<'SQL'
            ALTER TABLE [egresos].[importaciones]
            DROP CONSTRAINT [ck_egr_import_estado]
        SQL);
        DB::statement(<<<'SQL'
            ALTER TABLE [egresos].[importaciones]
            ADD CONSTRAINT [ck_egr_import_estado]
            CHECK ([estado] IN (N'pending', N'running', N'completed', N'failed', N'rolled_back'))
        SQL);
    }
};
