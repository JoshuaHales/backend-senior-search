<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ParkAndRide;
use App\Models\ParkingSpace;
use Illuminate\Support\Facades\Log;
use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSearchEndpointUsesGatewayRankingWithoutMock()
    {
        // Step 1: Create some User records
        $owner1 = User::factory()->create(['name' => 'bob']);
        $owner2 = User::factory()->create(['name' => 'phil']);
        $owner3 = User::factory()->create(['name' => 'matt']);

        // Step 2: Create ParkAndRide records
        ParkAndRide::factory()->create([
            'name' => 'Park n Ride 1',
            'lat' => 0.1,
            'lng' => 0.1,
            'user_id' => $owner1->id,
            'attraction_name' => 'disneyland',
            'location_description' => 'Main Street',
            'minutes_to_destination' => 10,
        ]);
        
        ParkAndRide::factory()->create([
            'name' => 'Park n Ride 3',
            'lat' => 0.1,
            'lng' => 0.1,
            'user_id' => $owner2->id,
            'attraction_name' => 'disneyland',
            'location_description' => 'Broadway',
            'minutes_to_destination' => 15,
        ]);

        // Step 3: Create ParkingSpace records
        ParkingSpace::factory()->create([
            'name' => 'Parking Space 1',
            'lat' => 0.1,
            'lng' => 0.1,
            'user_id' => $owner3->id,
            'no_of_spaces' => 3,
            'street_name' => 'Oxford Street',
            'city' => 'London',
            'space_details' => 'Driveway off street',
        ]);

        // Step 4: Call the API endpoint (Without mocking)
        $response = $this->get('/api/search?lat=0.1&lng=0.1');

        // Step 5: Assert the response is correct and ranked
        $response->assertStatus(200);

        // Step 6: Assert the response is correct and ranked
        $response->assertJsonCount(1);

        // Verify the correct ranking order in the response
        $response->assertJson([
            'data' => [
                ['name' => 'Park n Ride 1'], // This should be ranked first
                ['name' => 'Park n Ride 3'], // This should be ranked second 
                ['name' => 'Parking Space 1'], // This should be ranked third
            ],
        ]);
    }

    public function testSearchEndpointUsesGatewayRanking()
    {
        // Step 1: Create some User records
        $owner1 = User::factory()->create(['name' => 'bob']);
        $owner2 = User::factory()->create(['name' => 'phil']);
        $owner3 = User::factory()->create(['name' => 'matt']);
    
        // Step 2: Create ParkAndRide records
        $parkAndRide1 = ParkAndRide::factory()->create([
            'name' => 'Park n Ride 1',
            'lat' => 0.1,
            'lng' => 0.1,
            'user_id' => $owner1->id,
            'attraction_name' => 'disneyland',
            'location_description' => 'Main Street',
            'minutes_to_destination' => 10,
        ]);
        
        $parkAndRide2 = ParkAndRide::factory()->create([
            'name' => 'Park n Ride 3',
            'lat' => 0.1,
            'lng' => 0.1,
            'user_id' => $owner2->id,
            'attraction_name' => 'disneyland',
            'location_description' => 'Broadway',
            'minutes_to_destination' => 15,
        ]);
    
        // Step 3: Create ParkingSpace records
        $parkingSpace = ParkingSpace::factory()->create([
            'name' => 'Parking Space 1',
            'lat' => 0.1,
            'lng' => 0.1,
            'user_id' => $owner3->id,
            'no_of_spaces' => 3,
            'street_name' => 'Oxford Street',
            'city' => 'London',
            'space_details' => 'Driveway off street',
        ]);
    
        // Step 4: Mock the ParkAndRideRankerGateway
        app()->singleton(ParkAndRideRankerGateway::class, function () use ($parkAndRide1, $parkAndRide2) {
            $rankerGateway = $this->getMockBuilder(ParkAndRideRankerGateway::class)
                ->disableOriginalConstructor()
                ->getMock();
            
            // Return ranked ParkAndRide locations in the desired order (manually controlling the ranking)
            $rankerGateway->method('rank')->willReturn([$parkAndRide2, $parkAndRide1]); // Ranked order
            return $rankerGateway;
        });
    
        // Step 5: Mock the ParkingSpaceRankerGateway
        app()->singleton(ParkingSpaceRankerGateway::class, function () use ($parkingSpace) {
            $rankerGateway = $this->getMockBuilder(ParkingSpaceRankerGateway::class)
                ->disableOriginalConstructor()
                ->getMock();
            
            // Return ranked ParkingSpace locations in the desired order
            $rankerGateway->method('rank')->willReturn([$parkingSpace]);
            return $rankerGateway;
        });
    
        // Step 6: Call the API endpoint
        $response = $this->get('/api/search?lat=0.1&lng=0.1');
    
        // Step 7: Assert the response is correct and ranked
        $response->assertStatus(200);
    
        // Verify the correct ranking order in the response
        $response->assertJson([
            'data' => [
                ['name' => 'Park n Ride 3'],  // This should be ranked first (according to the mock)
                ['name' => 'Park n Ride 1'],  // This should be ranked second
                ['name' => 'Parking Space 1'], // This should be ranked third
            ],
        ]);
    }   

    public function testSearchEndpointIsHealthy()
    {
        // Step 1: Mock the ParkAndRideRankerGateway and configure it to return the original argument
        app()->singleton(ParkAndRideRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkAndRideRankerGateway::class)
                ->disableOriginalConstructor()
                ->getMock();
            
            $rankerGateway->method('rank')->willReturnArgument(0); // Return the input argument as output
            return $rankerGateway;
        });

        // Step 2: Mock the ParkingSpaceRankerGateway and configure it to return the original argument
        app()->singleton(ParkingSpaceRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkingSpaceRankerGateway::class)
                ->disableOriginalConstructor()
                ->getMock();
            
            $rankerGateway->method('rank')->willReturnArgument(0); // Return the input argument as output
            return $rankerGateway;
        });
    
        // Step 3: Send a GET request to the /api/search endpoint
        $response = $this->get('/api/search?lat=0.1&lng=0.1');

        // Step 4: Assert that the status code is 200
        $response->assertStatus(200);
    }

    public function testDetailsEndpoint()
    {
        // Step 1: Create a ParkAndRide record
        ParkAndRide::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'attraction_name' => 'disneyland',
            'location_description' => 'TCR',
            'minutes_to_destination' => 10,
        ]);

        // Step 2: Create a ParkingSpace record
        ParkingSpace::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'space_details' => 'Driveway off street',
            'city' => 'London',
            'street_name' => 'Oxford Street',
            'no_of_spaces' => 2,
        ]);

        // Step 3: Send a GET request to the /api/details endpoint
        $response = $this->get('/api/details?lat=0.1&lng=0.1');

        // Step 4: Assert that the status code is 200
        $response->assertStatus(200);

        // Step 5: Assert that the response matches the expected JSON structure
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