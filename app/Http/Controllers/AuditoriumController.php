<?php

namespace App\Http\Controllers;

use App\Models\Auditorium;
use Exception;
use Illuminate\Http\JsonResponse;

class AuditoriumController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Auditorium::all());
    }

    public function show($id): JsonResponse
    {
        try {
            if (! is_numeric($id) || $id <= 0) {
                throw new Exception('Invalid ID. Must be a positive integer.', 400);
            }

            $auditorium = Auditorium::findOrFail($id);

            return response()->json($auditorium);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
