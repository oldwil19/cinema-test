<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogExecutionTime
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;
        Log::info("execution time : {$executionTime} seconds", [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'execution_time' => $executionTime
        ]);

        return $response;
    }
}
