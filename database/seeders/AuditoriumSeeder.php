<?php

namespace Database\Seeders;

use App\Models\Auditorium;
use Illuminate\Database\Seeder;

class AuditoriumSeeder extends Seeder
{
    public function run()
    {
        if (Auditorium::count() > 0) {
            return;
        }

        for ($i = 1; $i <= 5; $i++) {
            Auditorium::create([
                'name' => "Auditorium $i",
                'seats' => $this->generateSeats(), // Se guarda como array real
                'status' => 'active',
                'opening_time' => '10:00:00',
                'closing_time' => '01:00:00',
            ]);
        }
    }

    private function generateSeats(): array
    {
        $rows = range('A', 'H'); // 8 filas (A-H)
        $seats = [];

        foreach ($rows as $row) {
            for ($num = 1; $num <= 15; $num++) { // 15 asientos por fila
                $seats[] = "$row$num";
            }
        }

        return $seats;
    }
}
