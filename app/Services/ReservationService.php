<?php

namespace App\Services;

use App\Contracts\ReservationServiceInterface;
use App\Models\Reservation;
use App\Models\Showtime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\ReservationCreated;
use Exception;
use Carbon\Carbon;
use App\Jobs\ExpireReservationJob;
use App\Jobs\ProcessReservationJob;
use Illuminate\Support\Facades\Bus;

class ReservationService implements ReservationServiceInterface
{
    /**
     * Reserve seats for a given showtime.
     */
    public function reserveSeats(int $showtimeId, array $seats): string
    {
        DB::beginTransaction();
        try {
            // Load showtime with auditorium to reduce queries
            $showtime = Showtime::with('auditorium')->where('id', $showtimeId)->lockForUpdate()->first();

            if (!$showtime) {
                throw new Exception("The selected showtime does not exist.", 404);
            }

            if ($this->isShowtimeExpired($showtime)) {
                throw new Exception("Cannot reserve seats for a past showtime.", 400);
            }

            if (!$this->seatsExistInAuditorium($showtime, $seats)) {
                throw new Exception("Some selected seats do not exist in this auditorium.", 400);
            }

            if ($this->seatsAreNotAvailable($showtime, $seats)) {
                throw new Exception("Some seats are already reserved or purchased.", 400);
            }

            if (!$this->areSeatsContiguous($seats)) {
                throw new Exception("Seats must be contiguous.", 400);
            }

            // Generate reservation ID before enqueueing
            $reservationId = (string) Str::uuid();

            // Dispatch the job with the generated reservation ID
            Bus::dispatch(new ProcessReservationJob($reservationId, $showtimeId, $seats));

            DB::commit();
            return $reservationId;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), $e->getCode() ?: 500);
        }
    }


    /**
     * Confirm a pending reservation.
     */
    public function confirmReservation(string $reservationId): bool
    {
        DB::beginTransaction();
        try {
            $reservation = Reservation::find($reservationId);

            if (!$reservation) {
                throw new Exception("Reservation not found.", 404);
            }

            if ($reservation->status !== 'pending') {
                throw new Exception("Reservation has already been confirmed or expired.", 400);
            }

            $reservation->update(['status' => 'confirmed']);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Expire all pending reservations past their expiration time.
     */
    public function expireReservations(): void
    {
        DB::transaction(function () {
            Reservation::where('status', 'pending')
                ->where('expires_at', '<', now())
                ->update(['status' => 'expired']);
        });
    }

    /**
     * Check if seats are already reserved.
     */
    private function seatsAreNotAvailable(Showtime $showtime, array $seats): bool
    {

        $reservedSeatsFromReservations = Reservation::where('showtime_id', $showtime->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->flatMap(fn($reservation) => json_decode($reservation->seats, true))
            ->toArray();


        $reservedSeatsFromShowtime = is_string($showtime->reserved_seats)
            ? json_decode($showtime->reserved_seats, true)
            : ($showtime->reserved_seats ?? []);


        $allReservedSeats = array_merge($reservedSeatsFromReservations, $reservedSeatsFromShowtime);


        return !empty(array_intersect($allReservedSeats, $seats));
    }

    /**
     * Check if seats are contiguous (same row and sequential numbers).
     */
    public function areSeatsContiguous(array $seats): bool
    {
        sort($seats);
        $rows = array_unique(array_map(fn($s) => preg_replace('/\d+/', '', $s), $seats));

        if (count($rows) > 1) {
            return false;
        }

        $numbers = array_map(fn($s) => (int) preg_replace('/\D/', '', $s), $seats);
        sort($numbers);

        for ($i = 1; $i < count($numbers); $i++) {
            if ($numbers[$i] !== $numbers[$i - 1] + 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve a reservation by its ID.
     */
    public function getReservationById(string $id): Reservation
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            throw new Exception("Reservation not found.", 404);
        }

        return $reservation;
    }

    /**
     * Get all reservations.
     */
    public function getAllReservation()
    {
        return Reservation::all();
    }

    /**
     * Check if seats exist in the auditorium.
     */
    private function seatsExistInAuditorium(Showtime $showtime, array $seats): bool
    {
        $auditoriumSeats = $showtime->auditorium->seats ?? [];

        return count(array_diff($seats, $auditoriumSeats)) === 0;
    }

    /**
     * Check if a showtime is still valid.
     */
    private function isShowtimeValid(Showtime $showtime): bool
    {
        return now()->lessThanOrEqualTo($showtime->start_time);
    }

    /**
     * Check if a showtime has already passed.
     */
    private function isShowtimeExpired(Showtime $showtime): bool
    {
        return now()->greaterThan($showtime->start_time);
    }
}
