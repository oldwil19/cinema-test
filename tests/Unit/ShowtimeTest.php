<?php
use Tests\TestCase;

test('validates contiguous seats', function () {
    $service = new App\Services\ReservationService();

    $this->assertTrue($service->areSeatsContiguous(['A1', 'A2', 'A3']));
    $this->assertFalse($service->areSeatsContiguous(['A1', 'A3', 'A5']));
});
