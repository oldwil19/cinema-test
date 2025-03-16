<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReservationCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        Log::info("Dispach ReservationCreated ID: {$reservation->id}");
        $this->reservation = $reservation;
    }

    public function viaQueue()
    {
        return 'reservations';
    }
}
