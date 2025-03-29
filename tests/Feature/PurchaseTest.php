<?php

use App\Models\Auditorium;
use App\Models\Reservation;
use App\Models\Showtime;
use Illuminate\Support\Str;

use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->auditorium = Auditorium::factory()->create([
        'name' => 'Main Auditorium',
        'seats' => ['A1', 'A2', 'A3', 'A4', 'A5'],
        'status' => 'active',
        'opening_time' => '09:00:00',
        'closing_time' => '23:00:00',
    ]);

    $this->showtime = Showtime::factory()->create([
        'movie_id' => 'tt1375666',
        'movie_title' => 'Inception',
        'auditorium_id' => $this->auditorium->id,
        'start_time' => now()->addDays(1),
        'available_seats' => ['A1', 'A2', 'A3', 'A4'],
        'reserved_seats' => [],
    ]);

    $this->reservation = Reservation::create([
        'id' => (string) Str::uuid(),
        'showtime_id' => $this->showtime->id,
        'seats' => ['A1', 'A2'],
        'status' => 'pending',
        'expires_at' => now()->addMinutes(10),
    ]);
});

it('can confirm a reservation purchase', function () {
    $response = postJson('/api/purchase', [
        'reservation_id' => $this->reservation->id,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Reservation successfully confirmed.']);

    // check if reservation changed to 'confirmed'
    $this->assertDatabaseHas('reservations', [
        'id' => $this->reservation->id,
        'status' => 'confirmed',
    ]);
    $this->assertDatabaseHas('payments', [
        'reservation_id' => $this->reservation->id,
        'status' => 'completed',
    ]);
});

it('returns error for missing reservation_id', function () {
    $response = postJson('/api/purchase', []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'error' => ['reservation_id'],
        ]);
});

it('returns error for invalid reservation_id format', function () {
    $response = postJson('/api/purchase', [
        'reservation_id' => 'invalid-uuid',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'error' => ['reservation_id'],
        ]);
});

it('returns error if reservation does not exist', function () {
    $response = postJson('/api/purchase', [
        'reservation_id' => (string) Str::uuid(),
    ]);

    $response->assertStatus(404)
        ->assertJson(['error' => 'Reservation not found.']);
});

it('returns error if reservation is already confirmed', function () {
    $this->reservation->update(['status' => 'confirmed']);

    $response = postJson('/api/purchase', [
        'reservation_id' => $this->reservation->id,
    ]);

    $response->assertStatus(400)
        ->assertJson(['error' => 'This reservation is not available for purchase.']);
});

it('returns error if reservation has expired', function () {
    $this->reservation->update(['expires_at' => now()->subMinutes(1)]);

    $response = postJson('/api/purchase', [
        'reservation_id' => $this->reservation->id,
    ]);

    $response->assertStatus(400)
        ->assertJson(['error' => 'This reservation has already expired.']);
});
