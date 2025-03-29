<?php

namespace App\Jobs;

use App\Events\ReservationCreated;
use App\Models\Reservation;
use App\Models\Showtime;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessReservationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $reservationId;

    protected int $showtimeId;

    protected array $seats;

    public function __construct(string $reservationId, int $showtimeId, array $seats)
    {
        $this->reservationId = $reservationId;
        $this->showtimeId = $showtimeId;
        $this->seats = $seats;
    }

    public function handle()
    {
        DB::beginTransaction();
        try {
            $showtime = Showtime::with('auditorium')->lockForUpdate()->find($this->showtimeId);

            if (! $showtime) {
                throw new Exception('The selected showtime does not exist.', 404);
            }

            if (now()->greaterThan($showtime->start_time)) {
                throw new Exception('Cannot reserve seats for a past showtime.', 400);
            }

            $reservedSeats = Reservation::where('showtime_id', $this->showtimeId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->get()
                ->flatMap(fn ($reservation) => json_decode($reservation->seats, true))
                ->toArray();

            if (! empty(array_intersect($reservedSeats, $this->seats))) {
                throw new Exception('Some seats are already reserved or purchased.', 400);
            }

            // Create reservation with the pre-generated reservation ID
            $reservation = Reservation::create([
                'id' => $this->reservationId,
                'showtime_id' => $this->showtimeId,
                'seats' => json_encode($this->seats),
                'status' => 'pending',
                'expires_at' => now()->addMinutes(env('RESERVATION_EXPIRATION_MINUTES', 10)),
            ]);

            event(new ReservationCreated($reservation));
            dispatch(new ExpireReservationJob($reservation->id))->delay($reservation->expires_at);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
