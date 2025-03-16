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

    //for check schedule of any auditorium
    public function findShowtime($auditoriumId, $startTime, $durationString)
    {
        preg_match('/\d+/', $durationString, $matches);
        $duration = isset($matches[0]) ? (int) $matches[0] : 120; // default time for test


        $endTime = Carbon::parse($startTime)->addMinutes($duration); // calculate endtime

        return Showtime::where('auditorium_id', $auditoriumId)
            ->where(function ($query) use ($startTime, $endTime, $duration) {
                $query->whereBetween('start_time', [$startTime, $endTime]) //  showtime
                    ->orWhere(function ($query) use ($startTime, $duration) {
                        $query->where('start_time', '<', $startTime) // if dont finish the last show or movie
                            ->whereRaw("ADDTIME(start_time, SEC_TO_TIME(? * 60)) > ?", [$duration, $startTime]);
                    });
            })
            ->exists();
    }

    public function findById(int $id): ?Showtime
    {
        return Showtime::find($id);
    }
}
