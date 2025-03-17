<?php

use App\Models\Auditorium;
use function Pest\Laravel\getJson;

beforeEach(function () {
    $this->auditorium = Auditorium::create([
        'name' => 'Test Auditorium',
        'seats' => ['A1', 'A2', 'A3'],
        'status' => 'active',
        'opening_time' => '09:00:00',
        'closing_time' => '23:00:00',
    ]);
});

test('it can list all auditoriums', function () {
    getJson('/api/auditoriums')
        ->assertOk()
        ->assertJsonStructure([
            '*' => ['id', 'name', 'seats', 'status', 'opening_time', 'closing_time']
        ]);
});

test('it can get an auditorium by valid ID', function () {
    getJson("/api/auditoriums/{$this->auditorium->id}")
        ->assertOk()
        ->assertJson([
            'id' => $this->auditorium->id,
            'name' => 'Test Auditorium',
        ]);
});

test('it returns 404 when retrieving non-existent auditorium', function () {
    getJson('/api/auditoriums/99999')
        ->assertStatus(404);
});

test('it returns 400 for invalid ID format', function () {
    getJson('/api/auditoriums/invalid_id')
        ->assertStatus(400)
        ->assertJson(['error' => 'Invalid ID. Must be a positive integer.']);
});
