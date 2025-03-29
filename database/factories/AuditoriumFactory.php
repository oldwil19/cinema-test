<?php

namespace Database\Factories;

use App\Models\Auditorium;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditoriumFactory extends Factory
{
    protected $model = Auditorium::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company.' Auditorium',
            'seats' => json_encode($this->generateSeats()),
            'status' => 'active',
            'opening_time' => '00:00:00',
            'closing_time' => '23:59:59',
        ];
    }

    private function generateSeats()
    {
        $rows = range('A', 'H'); // 8 filas
        $seats = [];

        foreach ($rows as $row) {
            for ($num = 1; $num <= 25; $num++) {
                $seats[] = "$row$num";
            }
        }

        return $seats;
    }
}
