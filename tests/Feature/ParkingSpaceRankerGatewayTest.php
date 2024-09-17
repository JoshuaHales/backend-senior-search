<?php

namespace Tests\Feature;

use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\ParkAndRide;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParkingSpaceRankerGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function testParkingSpaceRanker()
    {
        // TODO Part 2)

        $parkingSpace7 = ParkAndRide::factory()->create(['id' => 7]);
        $parkingSpace8 = ParkAndRide::factory()->create(['id' => 8]);
        $parkingSpace9 = ParkAndRide::factory()->create(['id' => 9]);

        /** @var ParkAndRideRankerGateway $gateway */
        $gateway = app(ParkingSpaceRankerGateway::class);

        $result = $gateway->rank([$parkingSpace7, $parkingSpace8, $parkingSpace9]);

        $this->assertEquals([$parkingSpace8, $parkingSpace9, $parkingSpace7], $result);
    }

    public function testSlowService()
    {
        // TODO Part 4)
    }
}
