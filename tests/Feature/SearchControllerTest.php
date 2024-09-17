<?php

namespace Tests\Feature;

use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\ParkAndRide;
use App\ParkingSpace;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSearchEndpointUsesGatewayRanking()
    {
        // TODO Part 2)
    }

    public function testSearchEndpointIsHealthy()
    {
        app()->singleton(ParkAndRideRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkAndRideRankerGateway::class)->disableOriginalConstructor()->getMock();
            $rankerGateway->method('rank')->willReturnArgument(0);
            return $rankerGateway;
        });

        app()->singleton(ParkingSpaceRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkingSpaceRankerGateway::class)->disableOriginalConstructor()->getMock();
            $rankerGateway->method('rank')->willReturnArgument(0);
            return $rankerGateway;
        });

        $response = $this->get('/api/search?lat=0.1&lng=0.1');

        $response->assertStatus(200);
    }

    public function testDetailsEndpoint()
    {
        ParkAndRide::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'attraction_name' => 'disneyland',
            'location_description' => 'TCR',
            'minutes_to_destination' => 10,
        ]);

        ParkingSpace::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'space_details' => 'Driveway off street',
            'city' => 'London',
            'street_name' => 'Oxford Street',
            'no_of_spaces' => 2,
        ]);

        $response = $this->get('/api/details?lat=0.1&lng=0.1');

        $response->assertStatus(200);
        $this->assertEquals(json_encode([
            [
                "description" => "Park and Ride to disneyland. (approx 10 minutes to destination)",
                "location_name" => "TCR"
            ],
            [
                "description" => "Parking space with 2 bays: Driveway off street",
                "location_name" => "Oxford Street, London"
            ]
        ]), $response->getContent());
    }
}
