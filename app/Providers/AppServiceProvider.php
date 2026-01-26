<?php

namespace App\Providers;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Lead;
use App\Models\Quote;
use App\Observers\ModelObserver;
use App\Observers\QuoteObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;


use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;
use Google\Client as GoogleClient;
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
        DB::enableQueryLog();

        Customer::observe(ModelObserver::class);
        Lead::observe(ModelObserver::class);
        Contract::observe(ModelObserver::class);
        Order::observe(ModelObserver::class);
        Quote::observe(ModelObserver::class);
        Quote::observe(QuoteObserver::class);

        Storage::extend('google', function ($app, $config) {
            $client = new GoogleClient();
            
            // Configuración OAuth 2.0
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            
            if (!empty($config['refreshToken'])) {
                $client->refreshToken($config['refreshToken']);
            }
            
            $client->addScope(Drive::DRIVE);
            $client->addScope(Drive::DRIVE_FILE);
            
            $service = new Drive($client);
            $adapter = new GoogleDriveAdapter($service, $config['folderId'] ?? 'root');

            // Retorna FilesystemAdapter de Laravel en lugar de Filesystem directo
            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
