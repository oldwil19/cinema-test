<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Showtime;

class ExpireReservationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $reservationId;

    public function __construct(string $reservationId)
    {
        $this->reservationId = $reservationId;
    }

    public function handle()
    {
        try {
            $reservation = Reservation::find($this->reservationId);

            if ($reservation && $reservation->status === 'pending') {
                // set expired a resevations
                $reservation->update(['status' => 'expired']);

                // remove reservation
                $showtime = Showtime::find($reservation->showtime_id);

                if ($showtime) {
                    $reservedSeats = is_string($showtime->reserved_seats)
                        ? json_decode($showtime->reserved_seats, true)
                        : ($showtime->reserved_seats ?? []);

                    $newReservedSeats = array_diff($reservedSeats, json_decode($reservation->seats, true));

                    $showtime->reserved_seats = json_encode(array_values($newReservedSeats));
                    $showtime->available_seats = json_encode(array_merge(
                        json_decode($showtime->available_seats, true) ?? [],
                        json_decode($reservation->seats, true)
                    ));

                    $showtime->save();
                }
            } elseif (!$reservation) {
                Log::warning("Reservation not found: {$this->reservationId}");
            }
        } catch (\Exception $e) {
            Log::error("Error expiring reservation {$this->reservationId}: " . $e->getMessage());
        }
    }
}
