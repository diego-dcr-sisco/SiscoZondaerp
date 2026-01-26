<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate para usuario Integral (acceso total)
        Gate::define('integral', function ($user) {
            return $user->type_id == 1;
        });

        // Gate para Client (acceso limitado)
        Gate::define('client', function ($user) {
            return $user->type_id == 2;
        });

        // Gate para rutas que solo Integral puede ver
        Gate::define('only-integral', function ($user) {
            return $user->type_id == 1;
        });
    }
}
