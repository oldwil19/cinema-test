<?php

namespace App\Repositories;

use App\Models\Showtime;
use App\Contracts\ShowtimeInterface;
use Carbon\Carbon;

class ShowtimeRepository implements ShowtimeInterface
{
    public function getAllShowtimes()
    {
        return Showtime::all();
    }

    public function createShowtime(array $data): Showtime
    {
        return Showtime::create($data);
    }

    public function existsInSameTimeSlot(int $auditoriumId, string $startTime): bool
    {
        return Showtime::where('auditorium_id', $auditoriumId)
            ->where('start_time', $startTime)
            ->exists();
    }

    // For checking schedule conflicts in an auditorium
    public function findShowtime($auditoriumId, $startTime, $durationString)
    {
        preg_match('/\d+/', $durationString, $matches);
        $duration = isset($matches[0]) ? (int) $matches[0] : 120; // Default 120 min

        $endTime = Carbon::parse($startTime)->addMinutes($duration);

        return Showtime::where('auditorium_id', $auditoriumId)
            ->where(function ($query) use ($startTime, $endTime, $duration) {
                $query->whereBetween('start_time', [$startTime, $endTime]) // Showtime
                    ->orWhere(function ($query) use ($startTime, $duration) {
                        $query->where('start_time', '<', $startTime)
                            ->whereRaw("ADDTIME(start_time, SEC_TO_TIME(? * 60)) > ?", [$duration, $startTime]);
                    });
            })
            ->exists();
    }

    public function findById(int $id): ?Showtime
    {
        return Showtime::find($id);
    }

    /**
     * Update available and reserved seats in a showtime.
     */
    public function updateShowtimeSeats(int $showtimeId, array $seatData)
    {
        return Showtime::where('id', $showtimeId)->update([
            'available_seats' => json_encode($seatData['available_seats']),
            'reserved_seats' => json_encode($seatData['reserved_seats']),
        ]);
    }
}
