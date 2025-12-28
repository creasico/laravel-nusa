<?php

declare(strict_types=1);

namespace Workbench\App\Console;

use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Models\Model;
use Creasi\Nusa\Support\GeometryHelpers;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GenerateStaticCommand extends Command
{
    use CommandHelpers, GeometryHelpers;

    protected $signature = 'nusa:generate-static
                            {target=resources/static : Target directory for generated files}
                            {--l|link= : Show link attribute to each generated files}';

    protected $description = 'Generate static files';

    /**
     * Execute the console command.
     */
    public function handle(Province $province): void
    {
        $target = (string) $this->libPath($this->argument('target'));

        $this->group("Generating static files to <fg=yellow>'{$target}'</>");

        File::ensureDirectoryExists($target);

        $this->writeToFile('provinces', $province->all(), [
            'provinces' => 'regencies',
            'regencies' => 'districts',
            'districts' => 'villages',
        ], $target);

        $this->endGroup();
    }

    /**
     * @param  Collection<int, Model>  $items
     */
    private function writeToFile(
        string $kind,
        Collection $items,
        array $steps,
        string $target,
        string ...$paths
    ): void {
        $link = $this->option('link');
        $tasks = [];

        if ($sub = $steps[$kind] ?? null) {
            $items->loadMissing($sub);

            unset($steps[$kind]);
        }

        $sanitizedItems = $this->sanitizeCollection($items, $link);
        $csvPath = $target.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $paths);

        if (empty($paths)) {
            $csvPath .= 'index';

            $this->writeJson($sanitizedItems, $csvPath);
        }

        $this->writeCsv($sanitizedItems, $csvPath);

        foreach ($items as $item) {
            $jsonPath = str_replace('.', DIRECTORY_SEPARATOR, $item->code);
            $filePath = $target.DIRECTORY_SEPARATOR.$jsonPath;
            $row = $geo = $item->except(['postal_codes']);
            $task = null;

            unset($row['coordinates']);

            if ($kind !== 'villages') {
                File::ensureDirectoryExists($filePath, recursive: true);
            }

            if ($sub) {
                $row[$sub] = $this->sanitizeCollection($item->$sub, $link);
                $task = fn () => $this->writeToFile($sub, $item->$sub, $steps, $target, ...explode('.', $item->code));
            }

            $this->writeJson($row, $filePath);

            if ($item->latitude && $item->longitude && $item->coordinates) {
                $this->writeGeoJson($kind, $geo, $filePath);
            }

            if ($kind === 'provinces') {
                $tasks[] = function () use ($task) {
                    // Reconnect to database on each concurrent tasks
                    DB::reconnect(config('creasi.nusa.connection', 'nusa'));

                    $task();
                };
            } else {
                value($task);
            }

            if (! $this->output->isVerbose()) {
                $this->output->write('<fg=green>•</>');

                continue;
            }

            $this->line(" - Writing: {$kind} '<fg=yellow>{$jsonPath}</>'", verbosity: 'v');
        }

        if (! empty($tasks)) {
            Concurrency::run($tasks);
        }
    }

    private function writeCsv(array $items, string $path): void
    {
        $lines = [];

        foreach ($items as $i => $item) {
            unset($item['href']);

            if ($i === 0) {
                $lines[] = array_keys($item);
            }

            $lines[] = array_values($item);
        }

        $fp = fopen("{$path}.csv", 'w');

        foreach ($lines as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);
    }

    private function writeJson(array $items, string $path): void
    {
        file_put_contents("{$path}.json", json_encode($items));

        if (! str_ends_with($path, 'index')) {
            File::ensureDirectoryExists($path, recursive: true);
            File::copy("{$path}.json", "{$path}/index.json");
        }
    }

    private function writeGeoJson(string $kind, array $value, string $path): void
    {
        $structure = $this->formatGeoJson(
            $kind,
            $value['code'],
            $value['name'],
            $value['longitude'],
            $value['latitude'],
            $value['coordinates']
        );

        file_put_contents("{$path}.geojson", json_encode($structure));
    }

    /**
     * @param  Collection<int, Model>  $collection
     */
    private function sanitizeCollection(Collection $collection, ?string $link = null): array
    {
        return $collection->map(function (Model $model) use ($link): array {
            $data = $model->except(['coordinates', 'postal_codes']);

            if ($link) {
                $path = str_replace('.', '/', $model->code);
                $data['href'] = "{$link}/{$path}.json";
            }

            return $data;
        })->all();
    }
}
