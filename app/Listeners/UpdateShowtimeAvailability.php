<?php

namespace App\Listeners;

use App\Events\ReservationCreated;
use App\Models\Showtime;
use Illuminate\Support\Facades\Log;

class UpdateShowtimeAvailability
{
    public function handle(ReservationCreated $event)
    {
        Log::info("Listeners  Showtime ID: {$event->reservation->showtime_id}, reservation ID: {$event->reservation->id}");
        $showtime = Showtime::find($event->reservation->showtime_id);

        if ($showtime) {
            // Obtener los asientos reservados de la reserva
            $reservedSeats = is_string($event->reservation->seats)
                ? json_decode($event->reservation->seats, true)
                : $event->reservation->seats;

            // Obtener los asientos disponibles y reservados actuales
            $availableSeats = is_string($showtime->available_seats)
                ? json_decode($showtime->available_seats, true)
                : ($showtime->available_seats ?? []);

            $existingReservedSeats = is_string($showtime->reserved_seats)
                ? json_decode($showtime->reserved_seats, true)
                : ($showtime->reserved_seats ?? []);

            // Remover los asientos reservados de available_seats
            $availableSeats = array_values(array_diff($availableSeats, $reservedSeats));

            // Agregar los asientos a reserved_seats
            $showtime->reserved_seats = json_encode(array_merge($existingReservedSeats, $reservedSeats));
            $showtime->available_seats = json_encode($availableSeats);

            $showtime->save();
            Log::info("Seats updated  Showtime ID: {$showtime->id}");
            Log::info("Now this seats are available: " . json_encode($availableSeats));
            Log::info("Reserved seats in this process" . json_encode($showtime->reserved_seats));
        }
    }
}
