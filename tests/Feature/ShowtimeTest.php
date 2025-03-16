<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use App\Models\Showtime;
use App\Models\Auditorium;
use Carbon\Carbon;

uses(RefreshDatabase::class);


beforeEach(function () {
    $this->auditorium = Auditorium::factory()->create();
});

function randomMovieTitle()
{
    $movies = [
        "Gladiator", "A Beautiful Mind", "Chicago", "The Lord of the Rings: The Return of the King",
        "Million Dollar Baby", "Crash", "The Departed", "No Country for Old Men",
        "Slumdog Millionaire", "The Hurt Locker", "The King's Speech", "The Artist",
        "Argo", "12 Years a Slave", "Birdman", "Spotlight", "Moonlight", "The Shape of Water",
        "Green Book", "Parasite", "Nomadland", "CODA", "Everything Everywhere All at Once"
    ];
    return $movies[array_rand($movies)];
}

function randomStartTime() {
    return Carbon::now()->addDays(rand(2, 10))->format('Y-m-d H:i:s');
}

it('puede obtener todos los showtimes', function () {
    Showtime::factory()->count(2)->create();

    $response = $this->getJson('/api/showtimes');

    $response->assertStatus(200)
        ->assertJsonCount(2);
});

it('puede crear un showtime con datos válidos', function () {
    Http::fake([
        'https://www.omdbapi.com/*' => Http::response([
            'imdbID' => 'tt1234567',
            'Title' => 'Inception',
        ], 200)
    ]);

    $response = $this->postJson('/api/showtimes', [
        'movie_title' => randomMovieTitle(),
        'auditorium_id' => $this->auditorium->id,
        'start_time' => randomStartTime(),
    ]);

    $response->dump();

    $response->assertStatus(201)
        ->assertJsonStructure([
            "id",
            "movie_id",
            "auditorium",
            "start_time",
            "available_seats",
            "reserved_seats"
        ]);

    // Asegurar que available_seats sea un array y no una cadena JSON
    $this->assertIsArray(json_decode($response['available_seats'], true));

    // Asegurar que reserved_seats sea un array vacío
    $this->assertIsArray($response['reserved_seats']);
    $this->assertEmpty($response['reserved_seats']);
});

it('devuelve error si la película no existe en OMDb', function () {
    Http::fake([
        'https://www.omdbapi.com/*' => Http::response([
            'Response' => 'False',
            'Error' => 'Movie not found!'
        ], 404)
    ]);

    $response = $this->postJson('/api/showtimes', [
        'movie_title' => "Película Falsa",
        'auditorium_id' => $this->auditorium->id,
        'start_time' => randomStartTime(),
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'Movie not found!'
        ]);
});

it('no puede duplicar showtimes en el mismo horario y auditorium', function () {
    $startTime = randomStartTime();

    Showtime::factory()->create([
        'auditorium_id' => $this->auditorium->id,
        'start_time' => $startTime,
    ]);

    $response = $this->postJson('/api/showtimes', [
        'movie_title' => randomMovieTitle(),
        'auditorium_id' => $this->auditorium->id,
        'start_time' => $startTime,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'error' => 'Este horario ya está ocupado en este auditorium.'
        ]);
});

