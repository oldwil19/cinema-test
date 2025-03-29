<?php

namespace App\Http\Controllers;

use App\Services\ShowtimeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShowtimeController extends Controller
{
    protected ShowtimeService $showtimeService;

    public function __construct(ShowtimeService $showtimeService)
    {
        $this->showtimeService = $showtimeService;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->showtimeService->getAllShowtimes());
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'movie_title' => 'required|string',
                'auditorium_id' => 'required|exists:auditoriums,id',
                'start_time' => 'required|date_format:Y-m-d H:i:s|after_or_equal:now',
            ]);

            $showtime = $this->showtimeService->createShowtime($validatedData);

            return response()->json($showtime, 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                throw new Exception('Invalid ID. Must be a positive integer.', 400);
            }

            $showtime = $this->showtimeService->getShow((int) $id);

            return response()->json($showtime);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            //  $updatedShowtime = $this->showtimeService->updateShowtime($id, $request->all());
            // return response()->json($updatedShowtime, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            //  $this->showtimeService->deleteShowtime($id);
            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
