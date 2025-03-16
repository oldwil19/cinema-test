<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class RateLimitService
{
    private int $dailyLimit;

    public function __construct()
    {
        $this->dailyLimit = env('OMDB_DAILY_LIMIT', 1000);
    }

    public function canMakeRequest(): bool
    {
        $requestsUsed = Redis::get('omdb_requests_used') ?? 0;
        return $requestsUsed < $this->dailyLimit;
    }

    public function incrementRequestCount(): void
    {
        Redis::incr('omdb_requests_used');
    }
}
