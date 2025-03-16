<?php

namespace App\Services;

use App\Contracts\ReservationServiceInterface;
use App\Models\Reservation;
use App\Models\Showtime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use App\Jobs\ExpireReservationJob;
use Carbon\Carbon;
use Exception;
use App\Events\ReservationCreated;

class ReservationService implements ReservationServiceInterface
{
    public function reserveSeats(int $showtimeId, array $seats): string
    {
        DB::beginTransaction();
        try {
            $showtime = Showtime::findOrFail($showtimeId);

            if ($this->isShowtimeExpired($showtime)) {
                throw new Exception("No se pueden reservar asientos para un showtime que ya ha pasado.");
            }

            if (!$this->seatsExistInAuditorium($showtime, $seats)) {
                throw new Exception("Los asientos seleccionados no existen en este auditorio.");
            }

            if (!$this->isShowtimeValid($showtime)) {
                throw new Exception("El showtime seleccionado ya no está disponible.");
            }

            if ($this->seatsAreNotAvailable($showtime, $seats)) {
                throw new Exception("Algunos asientos ya han sido reservados o comprados.");
            }

            if (!$this->areSeatsContiguous($seats)) {
                throw new Exception("Los asientos deben ser contiguos.");
            }

            $reservation = Reservation::create([
                'id' => Str::uuid(),
                'showtime_id' => $showtimeId,
                'seats' => json_encode($seats),
                'status' => 'pending',
                'expires_at' => now()->addMinutes(10),
            ]);

            event(new ReservationCreated($reservation));

            DB::commit();
            return $reservation->id;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function confirmReservation(string $reservationId): bool
    {
        DB::beginTransaction();
        try {
            $reservation = Reservation::findOrFail($reservationId);
            if ($reservation->status !== 'pending') {
                throw new Exception("La reserva ya fue confirmada o ha expirado.");
            }
            $reservation->update(['status' => 'confirmed']);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function expireReservations(): void
    {
        DB::transaction(function () {
            Reservation::where('status', 'pending')
                ->where('expires_at', '<', now())
                ->update(['status' => 'expired']);
        });
    }

    private function seatsAreNotAvailable(Showtime $showtime, array $seats): bool
    {
        $reservedSeats = Reservation::where('showtime_id', $showtime->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->flatMap(fn($reservation) => json_decode($reservation->seats, true))
            ->toArray();

        return !empty(array_intersect($reservedSeats, $seats));
    }

    private function areSeatsContiguous(array $seats): bool
    {
        sort($seats);
        $rows = array_unique(array_map(fn($s) => preg_replace('/\d+/', '', $s), $seats));
        if (count($rows) > 1) return false;

        $numbers = array_map(fn($s) => (int) preg_replace('/\D/', '', $s), $seats);
        sort($numbers);
        for ($i = 1; $i < count($numbers); $i++) {
            if ($numbers[$i] !== $numbers[$i - 1] + 1) {
                return false;
            }
        }
        return true;
    }
    public function getReservationById(string $id): Reservation
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            throw new Exception("Reserva no encontrada", 404);
        }

        return $reservation;
    }

    public function getAllReservation()
    {
        return Reservation::all();
    }


    private function seatsExistInAuditorium(Showtime $showtime, array $seats): bool
    {
        $auditoriumSeats = json_decode($showtime->auditorium->seats, true) ?? [];

        foreach ($seats as $seat) {
            if (!in_array($seat, $auditoriumSeats)) {
                return false;
            }
        }

        return true;
    }

    private function isShowtimeValid(Showtime $showtime): bool
    {
        return $showtime->start_time > now();
    }

    private function isShowtimeExpired(Showtime $showtime): bool
    {
        return now()->greaterThan($showtime->start_time);
    }
}
