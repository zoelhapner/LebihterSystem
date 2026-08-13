<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Pastikan schema ZH Picture tersedia
         */
        DB::statement('CREATE SCHEMA IF NOT EXISTS zhpicture');

        /*
         * Clone struktur tabel Finance dari public
         *
         * public = Finance LebihterSystem
         * zhpicture = Finance ZH Picture
         */

        $tables = [
            'accounting_accounts',
            'accounting_closing_balances',
            'accounting_journal_details',
            'accounting_journal_enclosures',
            'accounting_journals',
            'accounting_periods',
            'opening_balances',
        ];

        foreach ($tables as $table) {
            DB::statement("
                CREATE TABLE IF NOT EXISTS zhpicture.{$table}
                (LIKE public.{$table} INCLUDING ALL)
            ");
        }
    }

    public function down(): void
    {
        /*
         * Hapus tabel Finance ZH Picture
         */
        $tables = [
            'accounting_journal_details',
            'accounting_journal_enclosures',
            'accounting_journals',
            'accounting_closing_balances',
            'opening_balances',
            'accounting_periods',
            'accounting_accounts',
        ];

        foreach ($tables as $table) {
            DB::statement("
                DROP TABLE IF EXISTS zhpicture.{$table} CASCADE
            ");
        }
    }
};