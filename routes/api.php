<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ShowtimeController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AuditoriumController;
use App\Http\Controllers\PurchaseController;

Route::prefix('showtimes')->group(function () {
    Route::get('/', [ShowtimeController::class, 'index']);
    Route::post('/', [ShowtimeController::class, 'store']);
    Route::get('/{id}', [ShowtimeController::class, 'show']);
    Route::put('/{id}', [ShowtimeController::class, 'update']);
    Route::delete('/{id}', [ShowtimeController::class, 'destroy']);
});

Route::prefix('movies')->group(function () {
    Route::get('/', [MovieController::class, 'getMovie']);
});

Route::prefix('auditoriums')->group(function () {
    Route::get('/', [AuditoriumController::class, 'index']);
    Route::get('/{id}', [AuditoriumController::class, 'show']);
});

Route::prefix('reservations')->group(function () {
    Route::get('/', [ReservationController::class, 'index']);
    Route::get('/{id}', [ReservationController::class, 'show']);
    Route::post('/', [ReservationController::class, 'store']);
    Route::post('/{id}/confirm', [ReservationController::class, 'confirm']);
});



Route::post('/purchase', [PurchaseController::class, 'confirm']);
Route::get('/payments', [PurchaseController::class, 'index']);

Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando correctamente'], 200);
});

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        $mysqlStatus = 'OK';
    } catch (\Exception $e) {
        $mysqlStatus = 'ERROR';
    }

    try {
        Redis::ping();
        $redisStatus = 'OK';
    } catch (\Exception $e) {
        $redisStatus = 'ERROR';
    }

    return response()->json([
        'api' => 'OK',
        'mysql' => $mysqlStatus,
        'redis' => $redisStatus
    ]);
});
