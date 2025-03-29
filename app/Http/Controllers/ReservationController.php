<?php

namespace App\Http\Controllers;

use App\Services\ReservationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->reservationService->getAllReservation());
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'showtime_id' => 'required|exists:showtimes,id',
                'seats' => 'required|array|min:1',
                'seats.*' => 'string',
            ]);

            $reservationId = $this->reservationService->reserveSeats(
                $validatedData['showtime_id'],
                $validatedData['seats']
            );

            return response()->json([
                'reservation_id' => $reservationId,
                'message' => 'Reservation is being processed',
            ], 202);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|uuid',
            ]);

            if ($validator->fails()) {
                throw new Exception('Invalid ID. Must be a valid UUID.', 400);
            }

            $reservation = $this->reservationService->getReservationById($id);

            return response()->json($reservation);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
