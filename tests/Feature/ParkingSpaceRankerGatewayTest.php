<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ParkingSpace;
use Illuminate\Support\Facades\Log;
use App\ThirdParty\TimeoutException;
use App\ThirdParty\ParkingSpaceHttpService;
use App\Gateways\ParkingSpaceRankerGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParkingSpaceRankerGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function testParkingSpaceRanker()
    {
        // Step 1: Create ParkingSpace records with fixed attributes
        $parkingSpace7 = ParkingSpace::factory()->create([
            'id' => 1,
            'name' => 'Parking Space 1',
            'lat' => 40.7128,
            'lng' => -74.0060,
        ]);

        $parkingSpace8 = ParkingSpace::factory()->create([
            'id' => 3,
            'name' => 'Parking Space 3',
            'lat' => 34.0522,
            'lng' => -118.2437,
        ]);

        $parkingSpace9 = ParkingSpace::factory()->create([
            'id' => 4,
            'name' => 'Parking Space 4',
            'lat' => 51.5074,
            'lng' => -0.1278,
        ]);

        // Step 2: Initialize the ParkingSpaceRankerGateway
        /** @var ParkingSpaceRankerGateway $gateway */
        $gateway = app(ParkingSpaceRankerGateway::class);

        // Step 3: Call the rank method to rank the parking spaces
        $result = $gateway->rank([$parkingSpace7, $parkingSpace8, $parkingSpace9]);

        // Step 4: Assert that the returned ranking matches the expected ranking order
        $this->assertEquals([$parkingSpace7->id, $parkingSpace8->id, $parkingSpace9->id], array_column($result, 'id'));
    }

    public function testSlowService()
    {
        // Step 1: Mock the ParkingSpaceHttpService to simulate a slow response
        $mock = $this->getMockBuilder(ParkingSpaceHttpService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRanking'])
            ->getMock();

        // Step 2: Simulate a timeout by throwing a TimeoutException
        $mock->expects($this->once())
            ->method('getRanking')
            ->willThrowException(new TimeoutException());

        // Step 3: Use the mock in the ParkingSpaceRankerGateway
        $gateway = new ParkingSpaceRankerGateway($mock);

        // Step 4: Create three ParkingSpace objects to be ranked
        $parkingSpace1 = ParkingSpace::factory()->create(['id' => 1]);
        $parkingSpace2 = ParkingSpace::factory()->create(['id' => 2]);
        $parkingSpace3 = ParkingSpace::factory()->create(['id' => 3]);

        Log::shouldReceive('error')->once(); // Expect an error log in case of a timeout

        // Step 5: Call rank and verify that it does not throw an exception and returns the unranked list
        $result = $gateway->rank([$parkingSpace1, $parkingSpace2, $parkingSpace3]);

        // Step 6: Check that the result is still returned in the original order despite the timeout
        $this->assertEquals([$parkingSpace1->id, $parkingSpace2->id, $parkingSpace3->id], array_column($result, 'id'));
    }
}