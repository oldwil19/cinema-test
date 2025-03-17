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
     * Retrieves movie details from cache or API.
     */
    public function getMovieDetails(string $title): array
    {
        $cacheKey = "movie:$title";

        // Check if the movie exists in cache
        if ($this->cacheService->has($cacheKey)) {
            $movieData = $this->cacheService->get($cacheKey);
            $movieData['source'] = 'cache';
            Log::info("Movie retrieved from cache", ['title' => $title]);
            return $movieData;
        }

        // Check API rate limit
        if (!$this->rateLimitService->canMakeRequest()) {
            Log::warning("OMDb API request limit reached.");
            return ['error' => 'OMDb request limit exceeded', 'status' => 429];
        }

        // Fetch movie data from API
        $movieData = $this->movieApi->getMovieDetails($title);

        // Handle API errors
        if (isset($movieData['error'])) {
            Log::error("Error fetching movie from OMDb API", ['title' => $title, 'error' => $movieData['error']]);
            return $movieData;
        }

        // Store result in cache
        //$cacheDuration = config('services.cache_time_movie', 86400);
        $cacheDuration =  86400;
        $movieData['source'] = 'api';
        $this->cacheService->put($cacheKey, $movieData, $cacheDuration);

        // Increment API request count
        $this->rateLimitService->incrementRequestCount();
        Log::info("Movie retrieved from OMDb API", ['title' => $title, 'data' => $movieData]);

        return $movieData;
    }
}
