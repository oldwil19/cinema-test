<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogExecutionTime
{
    protected array $excludedRoutes = [
        'horizon/*',      // Excluye todo lo que empiece con "horizon/"
        'horizon/api/*',  // Excluye específicamente las APIs de Horizon
        'horizon/dashboard', // Excluye el dashboard de Horizon
    ];

    public function handle(Request $request, Closure $next)
    {
        $path = trim($request->path(), '/'); // Normalizar la ruta eliminando "/" inicial y final

        foreach ($this->excludedRoutes as $pattern) {
            if (Str::is($pattern, $path)) {
                return $next($request);
            }
        }

        $startTime = microtime(true);
        $response = $next($request);
        $endTime = microtime(true);

        Log::info('Execution time: '.($endTime - $startTime).' seconds for '.$path);

        return $response;
    }
}
