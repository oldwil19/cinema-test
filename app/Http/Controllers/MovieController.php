<?php

namespace App\Http\Controllers;

use App\Services\MovieDataService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    protected MovieDataService $movieDataService;

    public function __construct(MovieDataService $movieDataService)
    {
        $this->movieDataService = $movieDataService;
    }

    public function getMovie(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        return response()->json($this->movieDataService->getMovieDetails($request->title));
    }
}
