<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function handleException(Exception $e): JsonResponse
    {
        Log::error("Exception caught", ['error' => $e->getMessage()]);

        return match (true) {
            $e instanceof ValidationException => response()->json(['error' => $e->errors()], 422),
            $e instanceof ModelNotFoundException => response()->json(['error' => 'Resource not found.'], 404),
            default => response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500),
        };
    }
}
