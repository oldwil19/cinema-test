<?php

use App\Models\Reservation;
use App\Models\Showtime;
use Illuminate\Support\Str;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->auditorium = \App\Models\Auditorium::factory()->create([
        'name' => 'Main Auditorium',
        'seats' => ['A1', 'A2', 'A3', 'A4', 'A5', 'B1', 'B2', 'B3'],
        'status' => 'active',
        'opening_time' => '09:00:00',
        'closing_time' => '23:00:00',
    ]);

    $this->showtime = \App\Models\Showtime::factory()->create([
        'movie_id' => 'tt1375666',
        'movie_title' => 'Inception',
        'auditorium_id' => $this->auditorium->id,
        'start_time' => now()->addDays(1),
        'available_seats' => ['A1', 'A2', 'A3', 'A4'],
        'reserved_seats' => [],
    ]);
});

it('can list all reservations', function () {
    getJson('/api/reservations')
        ->assertOk()
        ->assertJsonStructure([
            '*' => ['id', 'showtime_id', 'seats', 'status', 'expires_at'],
        ]);
});

it('can retrieve a reservation by valid ID', function () {
    $reservation = Reservation::create([
        'id' => (string) Str::uuid(),
        'showtime_id' => $this->showtime->id,
        'seats' => ['A1', 'A2'],
        'status' => 'pending',
        'expires_at' => now()->addMinutes(10),
    ]);

    getJson("/api/reservations/{$reservation->id}")
        ->assertOk()
        ->assertJsonPath('id', $reservation->id);
});

it('returns 400 for invalid reservation ID format', function () {
    getJson('/api/reservations/invalid-id')
        ->assertStatus(400)
        ->assertJson(['error' => 'Invalid ID. Must be a valid UUID.']);
});

it('returns 404 for non-existent reservation', function () {
    getJson('/api/reservations/'.Str::uuid())
        ->assertStatus(404)
        ->assertJson(['error' => 'Reservation not found.']);
});

it('can create a new reservation', function () {
    postJson('/api/reservations', [
        'showtime_id' => $this->showtime->id,
        'seats' => ['A1', 'A2'],
    ])
        ->assertStatus(202)
        ->assertJsonStructure(['reservation_id', 'message']);
});

it('returns error if seats do not exist in the auditorium', function () {
    postJson('/api/reservations', [
        'showtime_id' => $this->showtime->id,
        'seats' => ['Z1', 'Z2'],
    ])
        ->assertStatus(400)
        ->assertJson(['error' => 'Some selected seats do not exist in this auditorium.']);
});

/*
it('returns error if seats are already reserved', function () {
    Reservation::create([
        'id' => (string) Str::uuid(),
        'showtime_id' => $this->showtime->id,
        'seats' => ['A1', 'A2'],
        'status' => 'pending',
        'expires_at' => now()->addMinutes(10),
    ]);



    postJson('/api/reservations', [
        'showtime_id' => $this->showtime->id,
        'seats' =>['A1']
    ])
        ->assertStatus(422)
        ->assertJson(['error' => 'Some seats are already reserved or purchased.']);

}); **/

it('returns error if seats are not contiguous', function () {
    postJson('/api/reservations', [
        'showtime_id' => $this->showtime->id,
        'seats' => ['A5', 'B3'],
    ])
        ->assertStatus(400)
        ->assertJson(['error' => 'Seats must be contiguous.']);
});

it('returns error if showtime is already past', function () {
    $pastShowtime = Showtime::create([
        'movie_id' => 'tt1375666',
        'movie_title' => 'Inception',
        'auditorium_id' => $this->auditorium->id,
        'start_time' => now()->subDays(1), // Showtime old
        'available_seats' => ['A1', 'A2', 'A3'],
        'reserved_seats' => [],
    ]);

    postJson('/api/reservations', [
        'showtime_id' => $pastShowtime->id,
        'seats' => ['A1', 'A2'],
    ])
        ->assertStatus(400)
        ->assertJson(['error' => 'Cannot reserve seats for a past showtime.']);
});
