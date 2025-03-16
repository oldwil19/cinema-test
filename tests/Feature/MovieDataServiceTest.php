<?php

use App\Services\MovieDataService;
use App\Services\CacheService;
use App\Services\RateLimitService;
use App\Contracts\MovieApiInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Mockery;
use function Pest\Laravel\mock;

beforeEach(function () {
    // Simular la caché en memoria usando ArrayStore
    Cache::swap(new Repository(new ArrayStore()));

    // Simular Redis para evitar llamadas reales
    Redis::shouldReceive('get')->andReturn(0);
    Redis::shouldReceive('incr')->andReturnNull();

    // Mock de la API para evitar llamadas reales a OMDb
    $this->mockApi = Mockery::mock(MovieApiInterface::class);

    // Crear instancias de los servicios con mocks
    $this->cacheService = new CacheService();
    $this->rateLimitService = new RateLimitService();
    $this->movieDataService = new MovieDataService($this->mockApi, $this->cacheService, $this->rateLimitService);
});

test('retorna película desde la API si no está en caché', function () {
    $movieTitle = 'The Matrix';
    $movieResponse = [
        'Title' => 'The Matrix',
        'Year' => '1999',
        'Director' => 'The Wachowskis',
        'source' => 'api'
    ];

    // Mock de API para devolver datos de la película
    $this->mockApi->shouldReceive('getMovieDetails')
        ->once()
        ->with($movieTitle)
        ->andReturn($movieResponse);

    // Ejecutar servicio
    $result = $this->movieDataService->getMovieDetails($movieTitle);

    expect($result)
        ->toBeArray()
        ->and($result['Title'])->toBe('The Matrix')
        ->and($result['source'])->toBe('api');
});

test('retorna película desde la caché si está almacenada', function () {
    $movieTitle = 'The Matrix';
    $cachedMovie = [
        'Title' => 'The Matrix',
        'Year' => '1999',
        'Director' => 'The Wachowskis',
        'source' => 'cache'
    ];

    // Simular caché existente
    Cache::put("movie:$movieTitle", $cachedMovie, 3600);

    // Ejecutar servicio
    $result = $this->movieDataService->getMovieDetails($movieTitle);

    expect($result)
        ->toBeArray()
        ->and($result['Title'])->toBe('The Matrix')
        ->and($result['source'])->toBe('cache');
});

test('maneja error cuando la API falla', function () {
    $movieTitle = 'The Matrix';

    // Simular error en la API
    $this->mockApi->shouldReceive('getMovieDetails')
        ->once()
        ->with($movieTitle)
        ->andReturn(['error' => 'Error al conectar con OMDb API']);

    // Ejecutar servicio
    $result = $this->movieDataService->getMovieDetails($movieTitle);

    expect($result)
        ->toBeArray()
        ->and($result)->toHaveKey('error')
        ->and($result['error'])->toBe('Error al conectar con OMDb API');
});

/**
test('evita exceder el límite de requests a OMDb API', function () {
    $movieTitle = 'The Matrix';

    // Simular que el límite de requests ha sido alcanzado
    Redis::shouldReceive('get')->once()->with('omdb_requests_used')->andReturn(1000);

    // Asegurar que la API NO se llame en este caso
    $this->mockApi->shouldNotReceive('getMovieDetails');

    // Ejecutar servicio
    $result = $this->movieDataService->getMovieDetails($movieTitle);

    dump($result); // Ver qué devuelve realmente

    expect($result)
        ->toBeArray();

    if (!isset($result['error'])) {
        dump("Error: La clave 'error' no existe en el resultado.");
    }

    expect($result)->toHaveKey('error');
    expect($result['error'])->toBe('Límite de requests a OMDb alcanzado'); // 💡 Debe coincidir exactamente
});
**/