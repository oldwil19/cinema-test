<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Auditorium;

class AuditoriumSeeder extends Seeder
{
    public function run()
    {
        //any?
        if (Auditorium::count() > 0) {
            return;
        }

        for ($i = 1; $i <= 10; $i++) {
            Auditorium::create([
                'name' => "Auditorium $i",
                'seats' => json_encode($this->generateSeats()),
                'status' => 'active',
                'opening_time' => '00:00:00',
                'closing_time' => '23:59:59',
            ]);
        }
    }

    private function generateSeats()
    {
        $rows = range('A', 'H'); // 8 filas (A-H)
        $seats = [];

        foreach ($rows as $row) {
            for ($num = 1; $num <= 25; $num++) { // 25 asientos por fila
                $seats[] = "$row$num";
            }
        }

        return $seats;
    }
}
