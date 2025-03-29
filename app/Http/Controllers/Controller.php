<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected function handleException(Exception $e): JsonResponse
    {
        Log::error('Exception caught', ['error' => $e->getMessage()]);

        return match (true) {
            $e instanceof ValidationException => response()->json(['error' => $e->errors()], 422),
            $e instanceof ModelNotFoundException => response()->json(['error' => 'Resource not found.'], 404),
            default => response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500),
        };
    }
}
