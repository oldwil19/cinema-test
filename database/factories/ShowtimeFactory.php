<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Showtime;
use App\Models\Auditorium;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Showtime>
 */
class ShowtimeFactory extends Factory
{
    protected $model = Showtime::class;

    public function definition()
    {
        return [
            'movie_id' => $this->faker->uuid, // Fake movie ID
            'movie_title' => $this->faker->sentence(3), // Random movie title
            'auditorium_id' => Auditorium::factory(), //create auditorium_id
            'start_time' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)), // Showtime en el futuro
            'available_seats' => json_encode(['A1', 'A2', 'A3', 'A4', 'A5']), // Fake seats
            'reserved_seats' => json_encode([]), // No hay reservaciones inicialmente
        ];
    }
}
