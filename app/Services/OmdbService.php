<?php

namespace App\Services;

use App\Contracts\MovieApiInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OmdbService implements MovieApiInterface
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.omdb.base_url', '');
        $this->apiKey = config('services.omdb.api_key', '');
    }

    public function getMovieDetails(string $title): array
    {
        try {
            $response = Http::get($this->apiUrl, [
                'apikey' => $this->apiKey,
                't' => $title,
            ]);

            $data = $response->json();

            if (!isset($data['Response'])) {
                Log::error("Error inesperado en OMDb API al buscar '{$title}': Respuesta inválida.");
                return [
                    'error' => 'Unexpected response from OMDb API',
                    'status' => 500
                ];
            }

            if ($data['Response'] === 'False') {
                Log::warning("Película no encontrada en OMDb: {$title}");
                return [
                    'error' => $data['Error'] ?? 'Movie not found!',
                    'status' => 404
                ];
            }

            return $data;
        } catch (\Exception $e) {
            Log::error("Error en OMDb API al buscar '{$title}': " . $e->getMessage());

            return [
                'error' => 'Error al conectar con OMDb API',
                'status' => 500
            ];
        }
    }
}
