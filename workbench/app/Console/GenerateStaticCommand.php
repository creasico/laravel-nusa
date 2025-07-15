<?php

declare(strict_types=1);

namespace Workbench\App\Console;

use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Models\Model;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Spatie\Fork\Fork;

class GenerateStaticCommand extends Command
{
    use CommandHelpers;

    protected $signature = 'nusa:generate-static
                            {target=resources/static : Target directory for generated files}
                            {--l|link= : Show link attribute to each generated files}';

    protected $description = 'Generate static files';

    /**
     * Execute the console command.
     */
    public function handle(Province $province): void
    {
        $this->group('Generating static files');

        File::ensureDirectoryExists(
            $target = (string) $this->libPath($this->argument('target'))
        );

        $provinces = $province->all();

        $this->write('provinces', $provinces, [
            'provinces' => 'regencies',
            'regencies' => 'districts',
            'districts' => 'villages',
        ], $target);

        $this->endGroup();
    }

    /**
     * @param  Collection<int, Model>  $items
     */
    private function write(
        string $kind,
        Collection $items,
        array $steps,
        string $target,
        string ...$paths
    ): void {
        $link = $this->option('link');
        // $sub = $steps[$kind] ?? null;
        $tasks = [];

        if ($sub = $steps[$kind] ?? null) {
            $items = $items->load($sub);

            unset($steps[$kind]);
        }

        $sanitizedItems = $this->sanitizeCollection($items, $link);
        $path = $target.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $paths);

        if (empty($paths)) {
            $path .= 'index';

            $this->writeJson($sanitizedItems, $path);
        }

        $this->writeCsv($sanitizedItems, $path);

        $this->line(
            string: " - Writing: '<fg=yellow>{$path}</>'",
            verbosity: 'v',
        );

        foreach ($items as $item) {
            $path = $target.DIRECTORY_SEPARATOR.str_replace('.', DIRECTORY_SEPARATOR, $item->code);
            $row = $geo = $item->attributesToArray();
            $task = null;

            unset($row['coordinates'], $row['postal_codes']);

            if ($kind !== 'villages') {
                File::ensureDirectoryExists($path, recursive: true);
            }

            if ($sub) {
                $row[$sub] = $this->sanitizeCollection($item->$sub, $link);
                $task = fn () => $this->write($sub, $item->$sub, $steps, $target, ...explode('.', $item->code));
            }

            $this->writeJson($row, $path);

            if ($item->latitude && $item->longitude && $item->coordinates) {
                $this->writeGeoJson($kind, $geo, $path);
            }

            if ($kind === 'provinces') {
                $tasks[] = $task;
            } else {
                value($task);
            }

            if (! $this->output->isVerbose()) {
                $this->output->write('<fg=green>•</>');

                continue;
            }

            $this->line(" - Writing: '<fg=yellow>{$path}</>'", verbosity: 'v');
        }

        if (! empty($tasks)) {
            Fork::new()->concurrent(4)->run(...$tasks);
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
    }

    private function writeGeoJson(string $kind, array $value, string $path): void
    {
        file_put_contents("{$path}.geojson", json_encode([
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'code' => $value['code'],
                        'kind' => $kind,
                        'name' => $value['name'],
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $value['longitude'],
                            $value['latitude'],
                        ],
                    ],
                ],
                [
                    'type' => 'Feature',
                    'properties' => [
                        'code' => $value['code'],
                        'kind' => $kind,
                        'name' => $value['name'],
                    ],
                    'geometry' => [
                        'type' => 'MultiPolygon',
                        'coordinates' => $value['coordinates'],
                    ],
                ],
            ],
        ]));
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
