<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use Illuminate\Support\Facades\DB;

class BackfillDeviceNormalizedCoordinates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devices:backfill-normalized-coords {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill normalized coordinates (x_norm, y_norm) for existing devices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting backfill process...');

        // Query devices that need normalized coordinates
        $devicesToUpdate = Device::where(function($query) {
                $query->whereNull('x_norm')
                      ->orWhereNull('y_norm');
            })
            ->whereNotNull('map_x')
            ->whereNotNull('map_y')
            ->whereNotNull('img_tamx')
            ->whereNotNull('img_tamy')
            ->where('img_tamx', '>', 0)
            ->where('img_tamy', '>', 0);

        $totalCount = $devicesToUpdate->count();

        if ($totalCount === 0) {
            $this->info('✅ No devices need to be updated.');
            return 0;
        }

        $this->info("Found {$totalCount} devices to update");

        $progressBar = $this->output->createProgressBar($totalCount);
        $progressBar->start();

        $updated = 0;
        $errors = 0;

        // Process in chunks to avoid memory issues
        $devicesToUpdate->chunk(100, function ($devices) use (&$updated, &$errors, $progressBar, $dryRun) {
            foreach ($devices as $device) {
                try {
                    // Additional safety check
                    if ($device->img_tamx <= 0 || $device->img_tamy <= 0) {
                        $this->warn("\nDevice ID {$device->id} has invalid dimensions: img_tamx={$device->img_tamx}, img_tamy={$device->img_tamy}");
                        $errors++;
                        $progressBar->advance();
                        continue;
                    }

                    $xNorm = (float) $device->map_x / (float) $device->img_tamx;
                    $yNorm = (float) $device->map_y / (float) $device->img_tamy;

                    // Validate normalized values (should be between 0 and 1)
                    if ($xNorm < 0 || $xNorm > 1 || $yNorm < 0 || $yNorm > 1) {
                        $this->warn("\nDevice ID {$device->id} has invalid normalized coords: x_norm={$xNorm}, y_norm={$yNorm}");
                        $errors++;
                        $progressBar->advance();
                        continue;
                    }

                    if (!$dryRun) {
                        $device->x_norm = $xNorm;
                        $device->y_norm = $yNorm;
                        $device->save();
                    }

                    $updated++;
                } catch (\Exception $e) {
                    $this->error("\nError updating device ID {$device->id}: " . $e->getMessage());
                    $errors++;
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("✅ Dry run completed: {$updated} devices would be updated");
        } else {
            $this->info("✅ Backfill completed: {$updated} devices updated successfully");
        }

        if ($errors > 0) {
            $this->warn("⚠️  {$errors} devices had errors or invalid values");
        }

        return 0;
    }
}
