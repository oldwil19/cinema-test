<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Showtime;
use App\Models\Auditorium;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShowtimeSeeder extends Seeder
{
    public function run()
    {
        // Eliminar registros sin truncar la tabla
        Showtime::query()->delete();

        $auditoriums = Auditorium::all();

        foreach ($auditoriums as $auditorium) {
            Showtime::create([
                'movie_id' => 'tt3896198',
                'movie_title' => 'Guardians of the Galaxy Vol. 2',
                'auditorium_id' => $auditorium->id,
                'start_time' => Carbon::now()->addDays(rand(1, 5))->format('Y-m-d H:i:s'),
                'available_seats' => $auditorium->seats,
                'reserved_seats' => [],
            ]);
        }
    }
}
