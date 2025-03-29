<?php

namespace App\Contracts;

interface ReservationServiceInterface
{
    public function reserveSeats(int $showtimeId, array $seats): string;

    public function confirmReservation(string $reservationId): bool;

    public function expireReservations(): void;
}
