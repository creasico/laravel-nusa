<?php

namespace Workbench\App\Console;

use Creasi\Nusa\Models;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Workbench\App\Support\GitHelper;

class DistCommand extends Command
{
    use CommandHelpers, GitHelper;

    protected $signature = 'nusa:dist {--force : Force overwrite existing distribution database}';

    protected $description = 'Create distribution database without coordinates column';

    public function handle()
    {
        $this->group('Creating distribution database');

        $branch = $this->currentBranch();

        $distPath = $this->libPath('database', "nusa.{$branch}.sqlite")->toString();
        $devPath = $this->libPath('database', "nusa.{$branch}-full.sqlite")->toString();

        if (! file_exists($devPath)) {
            $this->error("Development database not found at: {$devPath}");

            return 1;
        }

        if (file_exists($distPath) && ! $this->option('force')) {
            if (! $this->confirm('Distribution database already exists. Overwrite?')) {
                $this->info('Operation cancelled.');

                return 0;
            }
        }

        $this->info("Source: {$devPath}");
        $this->info("Target: {$distPath}");

        // Create backup of development database
        $backupPath = $this->createBackup($devPath);

        try {
            // Copy development database to distribution path
            $this->copyDatabase($devPath, $distPath);

            // Remove coordinates from distribution database
            $this->removeCoordinatesFromDistribution($distPath);

            $this->line('Distribution database created successfully!');
        } finally {
            // Always restore the development database from backup
            $this->restoreFromBackup($backupPath, $devPath);
        }

        $this->endGroup();

        return 0;
    }

    private function createBackup(string $devPath): string
    {
        $backupPath = $devPath.'.backup';

        $this->line("Creating backup: {$backupPath}");

        if (! copy($devPath, $backupPath)) {
            throw new \Exception('Failed to create backup of development database');
        }

        return $backupPath;
    }

    private function copyDatabase(string $source, string $destination): void
    {
        $this->line('Copying database to distribution path...');

        if (! copy($source, $destination)) {
            throw new \Exception('Failed to copy database to distribution path');
        }
    }

    private function removeCoordinatesFromDistribution(string $distPath): void
    {
        // Set up temporary connection to distribution database
        config([
            'database.connections.dist' => [
                'driver' => 'sqlite',
                'database' => $distPath,
                'foreign_key_constraints' => true,
            ],
        ]);

        foreach ($this->models() as $table => $modelClass) {
            $this->line("Removing coordinates from {$table}...");

            // Create a temporary model instance that uses the dist connection
            $model = new $modelClass;
            $model->setConnection('dist');

            // Update all records to set coordinates to empty string using raw query
            $count = DB::connection('dist')->table($table)->update(['coordinates' => null]);

            $this->line("  → {$count} records updated");
        }

        // Compact the database to reclaim space
        $this->line('Compacting database...');
        DB::connection('dist')->statement('VACUUM');
        $this->line('Database compacted successfully.');
    }

    private function restoreFromBackup(string $backupPath, string $devPath): void
    {
        $this->line('Restoring development database from backup...');

        if (! copy($backupPath, $devPath)) {
            $this->error('Failed to restore development database from backup!');
            $this->error("Your backup is available at: {$backupPath}");

            return;
        }

        // Clean up backup file
        unlink($backupPath);

        $this->line('Development database restored successfully.');
    }

    /**
     * @return array<string, class-string<Models\Model>>
     */
    private function models(): array
    {
        return [
            'provinces' => Models\Province::class,
            'regencies' => Models\Regency::class,
            'districts' => Models\District::class,
            'villages' => Models\Village::class,
        ];
    }
}
