<?php
use App\Models\Showtime;
use App\Models\Auditorium;
use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};

beforeEach(function () {
    $this->auditorium = Auditorium::create([
        'name' => 'Auditorium TEST',
        'seats' => ['A1', 'A2', 'A3'], 
        'status' => 'active',
        'opening_time' => '09:00:00',
        'closing_time' => '23:00:00',
    ]);
});

it('can list all showtimes', function () {
    getJson('/api/showtimes')->assertOk();
});

it('can retrieve a showtime by valid ID', function () {
    $showtime = Showtime::create([
        'movie_id' => 'tt0111161',
        'movie_title' => 'The Shawshank Redemption',
        'auditorium_id' => $this->auditorium->id,
        'start_time' => now()->addDays(1)->format('Y-m-d H:i:s'),
        'available_seats' => json_encode(['A1', 'A2']),
        'reserved_seats' => json_encode([]),
    ]);

    getJson("/api/showtimes/{$showtime->id}")
        ->assertOk()
        ->assertJsonPath('movie_title', 'The Shawshank Redemption');
});

it('returns 404 for non-existent showtime', function () {
    getJson('/api/showtimes/99999')->assertStatus(404);
});

it('can create a new showtime', function () {
    postJson('/api/showtimes', [
        'movie_title' => 'Inception',
        'auditorium_id' => $this->auditorium->id,
        'start_time' => now()->addDays(2)->format('Y-m-d H:i:s'),
    ])->assertStatus(201);
});



