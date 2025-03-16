<?php

namespace App\Services;

use App\Contracts\MovieApiInterface;
use App\Services\CacheService;
use App\Services\RateLimitService;
use Illuminate\Support\Facades\Log;

class MovieDataService
{
    private MovieApiInterface $movieApi;
    private CacheService $cacheService;
    private RateLimitService $rateLimitService;

    public function __construct(MovieApiInterface $movieApi, CacheService $cacheService, RateLimitService $rateLimitService)
    {
        $this->movieApi = $movieApi;
        $this->cacheService = $cacheService;
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * get movies details 
     */
    public function getMovieDetails(string $title): array
    {
        $cacheKey = "movie:$title";
    
        // check if the movie exist in redis
        if ($this->cacheService->has($cacheKey)) {
            $movieData = $this->cacheService->get($cacheKey);
            $movieData['source'] = 'cache';
            return $movieData;
        }
    
        // check request limit to OMDB API
        if (!$this->rateLimitService->canMakeRequest()) {
            Log::warning("Se alcanzó el límite de requests a OMDb API.");
            return ['error' => 'Límite de requests a OMDb alcanzado', 'status' => 429];
        }
    
        // feth from api
        $movieData = $this->movieApi->getMovieDetails($title);
    
        // any error 
        if (isset($movieData['error'])) {
            return $movieData;
        }
    
        // save in cache
        $movieData['source'] = 'api';
        //$this->cacheService->put($cacheKey, $movieData, config('services.cache_time_movie', 86400));
        $this->cacheService->put($cacheKey, $movieData,  86400);
    
        // discount quota available to OMDB
        $this->rateLimitService->incrementRequestCount();
        Log::info("Respuesta de OMDb", ['data' => json_encode($movieData)]);
        return $movieData;
    }
}
