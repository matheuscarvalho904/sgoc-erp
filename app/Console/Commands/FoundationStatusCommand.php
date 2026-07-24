<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class FoundationStatusCommand extends Command
{
    protected $signature = 'sgoc:foundation-status';

    protected $description = 'Verifica a instalaÃ§Ã£o da Foundation do SGOC ERP.';

    public function handle(): int
    {
        $this->newLine();
        $this->info('SGOC ERP - Foundation 0.1.0');

        try {
            $database = DB::selectOne('select current_database() as database');
            $this->line('Banco: '.$database->database);

            $schemas = DB::select("
                select schema_name
                from information_schema.schemata
                where schema_name in ('core', 'identity', 'access_control', 'workflow', 'documents', 'audit', 'integration')
                order by schema_name
            ");

            $this->line('Schemas: '.count($schemas).'/7');

            $tables = DB::select("
                select table_schema, table_name
                from information_schema.tables
                where table_schema = 'core'
                  and table_name in (
                    'tenants',
                    'organizations',
                    'companies',
                    'branches',
                    'works',
                    'departments',
                    'cost_centers',
                    'user_tenants'
                  )
                order by table_name
            ");

            $this->line('Tabelas Foundation: '.count($tables).'/8');

            $tenantCount = DB::table('core.tenants')->count();
            $this->line('Tenants cadastrados: '.$tenantCount);

            if (count($schemas) === 7 && count($tables) === 8) {
                $this->newLine();
                $this->info('Foundation instalada corretamente.');

                return self::SUCCESS;
            }

            $this->newLine();
            $this->warn('Foundation incompleta. Revise as migrations.');

            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}