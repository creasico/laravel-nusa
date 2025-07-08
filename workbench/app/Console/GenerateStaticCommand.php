<?php

declare(strict_types=1);

namespace Workbench\App\Console;

use Creasi\Nusa\Contracts\Province;
use Creasi\Nusa\Models\Model;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;

class GenerateStaticCommand extends Command
{
    use CommandHelpers;

    protected $signature = 'nusa:generate-static';

    protected $description = 'Generate static files';

    /**
     * Execute the console command.
     */
    public function handle(Province $provinceModel): void
    {
        $this->group('Generate static files');

        File::ensureDirectoryExists($this->libPath('resources/static'));

        $this->write('provinces', $provinceModel->all(), [
            'provinces' => 'regencies',
            'regencies' => 'districts',
            'districts' => 'villages',
        ]);

        $this->endGroup();
    }

    /**
     * @param  Collection<int, Model>  $items
     */
    private function write(string $kind, Collection $items, array $steps = [], string ...$prefix)
    {
        $sanitizedItems = $this->sanitizeCollection($items);

        if (empty($prefix)) {
            $prefix = ['index'];
        }

        $this->writeCsv($prefix, $sanitizedItems);
        $this->writeJson($prefix, $sanitizedItems);

        $subItems = $steps[$kind] ?? null;

        if ($subItems) {
            $items->load($subItems);
        }

        foreach ($items as $item) {
            $paths = explode('.', $item->code);

            if ($kind !== 'villages') {
                File::ensureDirectoryExists($this->libPath('resources/static/', ...$paths), recursive: true);
            }

            if ($subItems) {
                unset($steps[$kind]);

                $this->write($subItems, $item->{$subItems}, $steps, ...$paths);
            }

            $this->writeJson($paths, $item->except('coordinates', 'postal_codes'));

            if (! $item->latitude || ! $item->longitude || ! $item->coordinates) {
                continue;
            }

            $this->writeGeoJson($kind, $paths, $item->toArray());
        }
    }

    private function writeCsv(array $paths, array $items): void
    {
        $paths[count($paths) - 1] .= '.csv';
        $path = $this->libPath('resources/static', ...$paths);

        $this->line(" - Writing: '<fg=yellow>{$path->substr($this->libPath()->length() + 1)}</>'");

        $csv = [
            array_keys($items[0]),
        ];

        foreach ($items as $value) {
            $csv[] = array_values($value);
        }

        $fp = fopen((string) $path, 'w');

        foreach ($csv as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);
    }

    private function writeJson(array $paths, array $items): void
    {
        $paths[count($paths) - 1] .= '.json';
        $path = $this->libPath('resources/static', ...$paths);

        $this->line(" - Writing: '<fg=yellow>{$path->substr($this->libPath()->length() + 1)}</>'");

        file_put_contents((string) $path, json_encode($items, JSON_PRETTY_PRINT));
    }

    private function writeGeoJson(string $kind, array $paths, array $value): void
    {
        $paths[count($paths) - 1] .= '.geojson';
        $path = $this->libPath('resources/static', ...$paths);

        $this->line(" - Writing: '<fg=yellow>{$path->substr($this->libPath()->length() + 1)}</>'");

        file_put_contents((string) $path, json_encode([
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
    private function sanitizeCollection(Collection $collection): array
    {
        return $collection->map(function (Model $model) {
            return $model->except('coordinates', 'postal_codes');
        })->all();
    }
}
