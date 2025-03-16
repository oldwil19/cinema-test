<?php

namespace App\Http\Controllers;

use App\Models\Auditorium;
use Illuminate\Http\JsonResponse;

class AuditoriumController extends Controller
{
    public function index(): JsonResponse
    {
        $auditoriums = Auditorium::all();

        return response()->json($auditoriums);
    }
}
