<?php

namespace App\Contracts;

use App\Models\Showtime;

interface ShowtimeInterface
{
    public function getAllShowtimes();

    public function createShowtime(array $data): Showtime;
}
