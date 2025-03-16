<?php

namespace App\Http\Controllers;

use App\Services\ShowtimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ShowtimeController extends Controller
{
    protected ShowtimeService $showtimeService;

    public function __construct(ShowtimeService $showtimeService)
    {
        $this->showtimeService = $showtimeService;
    }

    public function index()
    {
        return response()->json($this->showtimeService->getAllShowtimes());
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'movie_title' => 'required|string',
                'auditorium_id' => 'required|exists:auditoriums,id',
                'start_time' => 'required|date_format:Y-m-d H:i:s',
            ]);

            Log::info("ShowtimeController@store llamado", ['request' => $validatedData]);

            $showtime = $this->showtimeService->createShowtime($validatedData);

            return response()->json([
                "id" => $showtime->id,
                "movie_id" => $showtime->movie_id,
                "auditorium" => $showtime->auditorium->name,
                "start_time" => $showtime->start_time,
                "available_seats" => $showtime->available_seats,
                "reserved_seats" => [],
            ], 201);
        } catch (Exception $e) {
            Log::error("Error en ShowtimeController@store", ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function show($id)
    {
        $showtime = $this->showtimeService->getShow($id);
        if (!$showtime) {
            return response()->json(['error' => 'Showtime no encontrado'], 404);
        }
        return response()->json($showtime);
    }
}
