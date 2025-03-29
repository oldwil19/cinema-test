<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Reservation;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseService
{
    /**
     * Confirm a reservation purchase.
     */
    public function confirmPurchase(string $reservationId): void
    {
        DB::beginTransaction();
        try {
            $reservation = Reservation::where('id', $reservationId)->lockForUpdate()->first();

            if (! $reservation) {
                throw new Exception('Reservation not found.', 404);
            }

            if ($reservation->status !== 'pending') {
                throw new Exception('This reservation is not available for purchase.', 400);
            }

            if ($reservation->expires_at < now()) {
                throw new Exception('This reservation has already expired.', 400);
            }

            // Marcar reserva como confirmada
            $reservation->update(['status' => 'confirmed']);

            // Registrar pago
            Payment::create([
                'id' => Str::uuid(),
                'reservation_id' => $reservationId,
                'status' => 'completed',
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all payments.
     */
    public function getAllPayments()
    {
        return Payment::with('reservation')->get();
    }
}
