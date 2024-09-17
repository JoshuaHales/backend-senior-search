<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ParkAndRide;
use Illuminate\Support\Facades\Log;
use App\ThirdParty\TimeoutException;
use App\Gateways\ParkAndRideRankerGateway;
use App\ThirdParty\ParkAndRide\ParkAndRideSDK;
use App\ThirdParty\ParkAndRide\RankingRequest;
use App\ThirdParty\ParkAndRide\RankingResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParkAndRideGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function testParkAndRideSDK()
    {
        // Step 1: Mock the ParkAndRideSDK and only mock the 'getRankingResponse' method
        $mock = $this->getMockBuilder(ParkAndRideSDK::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRankingResponse'])
            ->getMock();

        // Step 2: Mock the RankingRequest and only mock the 'RankingResponse' method
        $mockRequest = new RankingRequest([1, 2, 3]);
        $mockResponse = $this->getMockBuilder(RankingResponse::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResult'])
            ->getMock();

        // Step 3: Return a ranked array with the correct format
        $mockResponse->expects($this->once())
            ->method('getResult')
            ->willReturn([
                ['park_and_ride_id' => 1, 'rank' => 0],
                ['park_and_ride_id' => 2, 'rank' => 1],
                ['park_and_ride_id' => 3, 'rank' => 2]
            ]);

        // Step 4: Configure the SDK mock to return the mocked RankingResponse
        $mock->expects($this->once())
            ->method('getRankingResponse')
            ->willReturn($mockResponse);

        // Use the mock in the gateway
        $gateway = new ParkAndRideRankerGateway($mock);

        // Call the 'rank' method in the gateway
        $result = $gateway->rank([['id' => 1], ['id' => 2], ['id' => 3]]);

        // Step 4: Assert that 3 items are returned
        $this->assertCount(3, $result);
    }

    public function testSlowService()
    {
        // Step 1: Mock the ParkingSpaceHttpService to simulate a slow response
        $mock = $this->getMockBuilder(ParkAndRideSDK::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRankingResponse'])
            ->getMock();

        // Step 2: Simulate a timeout by throwing a TimeoutException
        $mock->expects($this->once())
            ->method('getRankingResponse')
            ->willThrowException(new TimeoutException());

        // Step 3: Use the mock in the ParkAndRideRankerGateway
        $gateway = new ParkAndRideRankerGateway($mock);

        // Step 4: Create three ParkingSpace objects to be ranked
        $parkAndRide1 = ['id' => 1];
        $parkAndRide2 = ['id' => 2];
        $parkAndRide3 = ['id' => 3];

        Log::shouldReceive('error')->once(); // Expect an error log in case of a timeout

        // Step 5: Call rank and verify that it does not throw an exception and returns the unranked list
        $result = $gateway->rank([$parkAndRide1, $parkAndRide2, $parkAndRide3]);

        // Step 6: Check that the result is still returned in the original order despite the timeout
        $this->assertEquals([$parkAndRide1, $parkAndRide2, $parkAndRide3], $result);
    }
}