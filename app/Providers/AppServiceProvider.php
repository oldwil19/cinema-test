<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\MovieApiInterface;
use App\Services\OmdbService;
use App\Services\ReservationService;
use App\Contracts\ReservationServiceInterface;

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
            ReservationServiceInterface::class, ReservationService::class
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
