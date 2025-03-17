<?php

namespace App\Http\Controllers;

use App\Services\MovieDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class MovieController extends Controller
{
    protected MovieDataService $movieDataService;

    public function __construct(MovieDataService $movieDataService)
    {
        $this->movieDataService = $movieDataService;
    }

    public function getMovie(Request $request): JsonResponse
    {
        try {
            $title = $request->query('title');

            if (!$title) {
                throw new Exception("Movie title is required.", 400);
            }

            $movie = $this->movieDataService->getMovieDetails($title);
            return response()->json($movie);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
