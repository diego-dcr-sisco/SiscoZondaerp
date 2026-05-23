<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemOperator;

class ExportClientSystemTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client-system:export-tree
                            {--disk=google : Source storage disk to scan}
                            {--root=client_system : Root path to export}
                            {--output-dir=exports/client-system-tree : Local directory (inside storage/app) for txt files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export client system folder tree to a daily txt file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startedAt = microtime(true);
        $diskName = (string) $this->option('disk');
        $root = trim((string) $this->option('root'), '/');
        $outputDir = trim((string) $this->option('output-dir'), '/');

        if ($root === '') {
            $this->error('The --root option cannot be empty.');
            return self::FAILURE;
        }

        try {
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk($diskName);

            /** @var FilesystemOperator $driver */
            $driver = $disk->getDriver();

            if (!$driver->directoryExists($root)) {
                $this->error("Root path [{$root}] does not exist in disk [{$diskName}].");
                return self::FAILURE;
            }

            $items = $driver->listContents($root, true)->toArray();
            [$tree, $dirCount, $fileCount] = $this->buildTree($items, $root);

            $generatedAt = Carbon::now(config('app.timezone'));
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            $content = $this->renderReport(
                root: $root,
                diskName: $diskName,
                generatedAt: $generatedAt,
                dirCount: $dirCount,
                fileCount: $fileCount,
                durationMs: $durationMs,
                tree: $tree
            );

            $fileName = sprintf('client_system_tree_%s.txt', $generatedAt->format('Y-m-d_His'));
            $targetPath = $outputDir . '/' . $fileName;

            Storage::disk('local')->put($targetPath, $content);

            $this->info("Tree exported successfully: storage/app/{$targetPath}");
            $this->line("Directories: {$dirCount} | Files: {$fileCount} | Duration: {$durationMs} ms");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Client system tree export failed.', [
                'disk' => $diskName,
                'root' => $root,
                'message' => $e->getMessage(),
            ]);

            $this->error('Export failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * @param array<int, mixed> $items
     * @return array{0: array<string, mixed>, 1: int, 2: int}
     */
    private function buildTree(array $items, string $root): array
    {
        $tree = [
            'dirs' => [],
            'files' => [],
        ];

        $dirCount = 0;
        $fileCount = 0;

        foreach ($items as $item) {
            $path = trim((string) $item->path(), '/');

            if ($path === '' || $path === $root) {
                continue;
            }

            $relative = ltrim(substr($path, strlen($root)), '/');
            if ($relative === '') {
                continue;
            }

            $segments = array_values(array_filter(explode('/', $relative), static fn($part) => $part !== ''));
            if (empty($segments)) {
                continue;
            }

            if ($item->isDir()) {
                $dirCount++;
                $this->insertDirectory($tree, $segments);
                continue;
            }

            $fileCount++;
            $this->insertFile($tree, $segments);
        }

        $this->sortTree($tree);

        return [$tree, $dirCount, $fileCount];
    }

    /**
     * @param array<string, mixed> $node
     * @param array<int, string> $segments
     */
    private function insertDirectory(array &$node, array $segments): void
    {
        $current = &$node;

        foreach ($segments as $segment) {
            if (!isset($current['dirs'][$segment])) {
                $current['dirs'][$segment] = [
                    'dirs' => [],
                    'files' => [],
                ];
            }

            $current = &$current['dirs'][$segment];
        }
    }

    /**
     * @param array<string, mixed> $node
     * @param array<int, string> $segments
     */
    private function insertFile(array &$node, array $segments): void
    {
        $fileName = array_pop($segments);
        if ($fileName === null || $fileName === '') {
            return;
        }

        $current = &$node;
        foreach ($segments as $segment) {
            if (!isset($current['dirs'][$segment])) {
                $current['dirs'][$segment] = [
                    'dirs' => [],
                    'files' => [],
                ];
            }

            $current = &$current['dirs'][$segment];
        }

        if (!in_array($fileName, $current['files'], true)) {
            $current['files'][] = $fileName;
        }
    }

    /**
     * @param array<string, mixed> $node
     */
    private function sortTree(array &$node): void
    {
        if (!empty($node['dirs'])) {
            ksort($node['dirs'], SORT_NATURAL | SORT_FLAG_CASE);
            foreach ($node['dirs'] as &$child) {
                $this->sortTree($child);
            }
            unset($child);
        }

        if (!empty($node['files'])) {
            sort($node['files'], SORT_NATURAL | SORT_FLAG_CASE);
        }
    }

    /**
     * @param array<string, mixed> $tree
     */
    private function renderReport(
        string $root,
        string $diskName,
        Carbon $generatedAt,
        int $dirCount,
        int $fileCount,
        int $durationMs,
        array $tree
    ): string {
        $lines = [];
        $lines[] = 'Client System Tree Export';
        $lines[] = '=========================';
        $lines[] = 'Generated at : ' . $generatedAt->format('Y-m-d H:i:s');
        $lines[] = 'Timezone     : ' . config('app.timezone');
        $lines[] = 'Disk         : ' . $diskName;
        $lines[] = 'Root         : ' . $root;
        $lines[] = 'Directories  : ' . $dirCount;
        $lines[] = 'Files        : ' . $fileCount;
        $lines[] = 'Duration (ms): ' . $durationMs;
        $lines[] = '';
        $lines[] = $root . '/';

        $treeLines = $this->renderTreeLines($tree, '');
        foreach ($treeLines as $line) {
            $lines[] = $line;
        }

        $lines[] = '';

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param array<string, mixed> $node
     * @return array<int, string>
     */
    private function renderTreeLines(array $node, string $prefix): array
    {
        $lines = [];

        $entries = [];
        foreach ($node['dirs'] as $name => $childNode) {
            $entries[] = [
                'type' => 'dir',
                'name' => (string) $name,
                'node' => $childNode,
            ];
        }

        foreach ($node['files'] as $name) {
            $entries[] = [
                'type' => 'file',
                'name' => (string) $name,
                'node' => null,
            ];
        }

        $lastIndex = count($entries) - 1;

        foreach ($entries as $index => $entry) {
            $isLast = $index === $lastIndex;
            $connector = $isLast ? '`-- ' : '|-- ';
            $childPrefix = $prefix . ($isLast ? '    ' : '|   ');

            if ($entry['type'] === 'dir') {
                $lines[] = $prefix . $connector . $entry['name'] . '/';
                $nested = $this->renderTreeLines($entry['node'], $childPrefix);
                foreach ($nested as $nestedLine) {
                    $lines[] = $nestedLine;
                }
                continue;
            }

            $lines[] = $prefix . $connector . $entry['name'];
        }

        return $lines;
    }
}
