<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SCHEMAS = [
        'core',
        'identity',
        'access_control',
        'workflow',
        'documents',
        'audit',
        'integration',
    ];

    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
        DB::statement('CREATE EXTENSION IF NOT EXISTS citext');

        foreach (self::SCHEMAS as $schema) {
            DB::statement(sprintf('CREATE SCHEMA IF NOT EXISTS "%s"', $schema));
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::SCHEMAS) as $schema) {
            DB::statement(sprintf('DROP SCHEMA IF EXISTS "%s" CASCADE', $schema));
        }
    }
};