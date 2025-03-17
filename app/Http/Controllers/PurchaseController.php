<?php

namespace App\Http\Controllers;

use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class PurchaseController extends Controller
{
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * Confirm a reservation purchase.
     */
    public function confirm(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'reservation_id' => 'required|uuid',
            ]);

            $this->purchaseService->confirmPurchase($validatedData['reservation_id']);

            return response()->json(['message' => 'Reservation successfully confirmed.'], 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get all payments.
     */
    public function index(): JsonResponse
    {
        return response()->json($this->purchaseService->getAllPayments());
    }
}
