<?php

namespace App\Providers;

use App\Exceptions\GoogleDriveAuthException;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\Tracking;
use App\Observers\ModelObserver;
use App\Observers\QuoteObserver;
use App\Services\GoogleDriveClientFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;
use Google\Service\Drive;
use Illuminate\Filesystem\FilesystemAdapter;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*if ($this->app->environment('local')) {
            DB::enableQueryLog();
        }

        Customer::observe(ModelObserver::class);
        Lead::observe(ModelObserver::class);
        Contract::observe(ModelObserver::class);
        Order::observe(ModelObserver::class);
        Quote::observe(ModelObserver::class);
        Quote::observe(QuoteObserver::class);
        Tracking::observe(ModelObserver::class);*/

        if (env('LOG_SLOW_QUERIES', false)) {
            DB::listen(function ($query) {
                if ($query->time > 500) {
                    $context = [
                        'sql' => $query->sql,
                        'time_ms' => $query->time,
                        'bindings' => $query->bindings,
                        'url' => request()->fullUrl(),
                        'route' => request()->route()?->getName(),
                    ];

                    Log::warning('Query lenta detectada', $context);
                }
            });
        }

        Storage::extend('google', function ($app, $config) {
            $client = $app->make(GoogleDriveClientFactory::class)->makeAuthenticatedClient($config);

            try {
                $service = new Drive($client);
                $adapter = new GoogleDriveAdapter($service, $config['folderId'] ?? 'root');

                // Retorna FilesystemAdapter de Laravel en lugar de Filesystem directo
                return new FilesystemAdapter(
                    new Filesystem($adapter, $config),
                    $adapter,
                    $config
                );
            } catch (\Throwable $e) {
                Log::error('Google Drive adapter initialization failed.', [
                    'message' => $e->getMessage(),
                ]);

                throw new GoogleDriveAuthException(
                    'Ocurrió un problema al intentar acceder a tus Carpetas MIP. ',
                    previous: $e
                );
            }
        });
    }
}
