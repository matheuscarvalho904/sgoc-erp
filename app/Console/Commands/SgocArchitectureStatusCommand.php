<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Shared\Models\BaseModel;
use App\Shared\Models\SnapshotModel;
use App\Shared\Models\TransactionModel;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;

final class SgocArchitectureStatusCommand extends Command
{
    protected $signature = 'sgoc:architecture-status';
    protected $description = 'Valida a classificação de ciclo de vida dos models SGOC.';

    public function handle(): int
    {
        $rows = [];
        $errors = 0;

        foreach ($this->modelClasses() as $class) {
            $reflection = new ReflectionClass($class);
            if ($reflection->isAbstract()) {
                continue;
            }

            $kind = match (true) {
                is_subclass_of($class, SnapshotModel::class) => 'Snapshot',
                is_subclass_of($class, TransactionModel::class) => 'Transação',
                is_subclass_of($class, BaseModel::class) => 'Cadastro',
                default => 'Não classificado',
            };

            $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($class), true);
            $valid = $kind === 'Cadastro' ? $softDeletes : ! $softDeletes;
            $errors += $valid ? 0 : 1;

            $rows[] = [$reflection->getShortName(), $kind, $softDeletes ? 'Sim' : 'Não', $valid ? 'OK' : 'REVISAR'];
        }

        $this->table(['Model', 'Classe', 'Soft delete', 'Resultado'], $rows);

        if ($errors > 0) {
            $this->error("Foram encontradas {$errors} inconsistências de arquitetura.");
            return self::FAILURE;
        }

        $this->info('Arquitetura de ciclo de vida validada com sucesso.');
        return self::SUCCESS;
    }

    /** @return list<class-string> */
    private function modelClasses(): array
    {
        $root = app_path('Modules');
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)),
            '/Domain[\\\\\/]Models[\\\\\/].+\.php$/i',
        );

        $classes = [];
        foreach ($iterator as $file) {
            $relative = str_replace([app_path().DIRECTORY_SEPARATOR, '.php', DIRECTORY_SEPARATOR], ['', '', '\\'], $file->getPathname());
            $class = 'App\\'.$relative;
            if (class_exists($class)) {
                $classes[] = $class;
            }
        }

        sort($classes);
        return $classes;
    }
}
