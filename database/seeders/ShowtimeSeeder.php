<?php

namespace Database\Seeders;

use App\Models\Auditorium;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShowtimeSeeder extends Seeder
{
    public function run()
    {
        if (Showtime::count() >= 3) {
            return;
        }

        $auditoriums = Auditorium::inRandomOrder()->take(3)->get();

        foreach ($auditoriums as $auditorium) {
            Showtime::create([
                'movie_id' => 'tt3896198',
                'movie_title' => 'Guardians of the Galaxy Vol. 2',
                'auditorium_id' => $auditorium->id,
                'start_time' => Carbon::now()->addDays(rand(1, 5))->format('Y-m-d H:i:s'),
                'available_seats' => $auditorium->seats, // 🔹 No usar json_decode()
                'reserved_seats' => [],
            ]);
        }
    }
}
