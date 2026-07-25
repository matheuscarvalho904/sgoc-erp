<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class IdentityAccessStatusCommand extends Command
{
    protected $signature = 'sgoc:identity-status';

    protected $description = 'Verifica a instalaÃ§Ã£o do mÃ³dulo Identity & Access Control.';

    public function handle(): int
    {
        try {
            $tables = DB::select("
                select table_name
                from information_schema.tables
                where table_schema = 'access_control'
                  and table_name in (
                    'roles',
                    'permissions',
                    'role_permissions',
                    'user_roles',
                    'user_scopes',
                    'segregation_rules',
                    'access_exceptions'
                  )
            ");

            $roles = DB::table('access_control.roles')->count();
            $permissions = DB::table('access_control.permissions')->count();
            $assignments = DB::table('access_control.user_roles')->count();

            $this->newLine();
            $this->info('SGOC ERP - Identity & Access Control 0.2.1');
            $this->line('Tabelas: '.count($tables).'/7');
            $this->line('Perfis: '.$roles);
            $this->line('PermissÃµes: '.$permissions);
            $this->line('VÃ­nculos usuÃ¡rio/perfil: '.$assignments);

            if (count($tables) === 7 && $roles >= 6 && $permissions >= 100) {
                $this->newLine();
                $this->info('Identity & Access Control instalado corretamente.');

                return self::SUCCESS;
            }

            $this->warn('InstalaÃ§Ã£o incompleta.');

            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}