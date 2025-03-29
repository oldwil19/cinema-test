<?php

namespace App\Providers;

use App\Contracts\MovieApiInterface;
use App\Contracts\ReservationServiceInterface;
use App\Services\OmdbService;
use App\Services\ReservationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(
            MovieApiInterface::class,
            OmdbService::class,
            ReservationServiceInterface::class,
            ReservationService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
