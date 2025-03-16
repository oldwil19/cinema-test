<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    public function get(string $key): mixed
    {
        return Cache::get($key);
    }

    public function put(string $key, mixed $value, int $seconds): void
    {
        Cache::put($key, $value, $seconds);
    }
}
