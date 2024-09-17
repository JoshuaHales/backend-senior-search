<?php

namespace App\Http\Controllers;

use App\SearchService;
use App\Models\ParkAndRide;
use App\Models\ParkingSpace;
use App\Http\Resources\Location;
use Illuminate\Support\Facades\Log;
use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\Http\Requests\ValidateLocationRequest;

class SearchController extends Controller
{
    public function index(
        ValidateLocationRequest $request,
        SearchService $searchService,
        ParkAndRideRankerGateway $parkAndRideGateway,
        ParkingSpaceRankerGateway $parkingSpaceGateway
    ) {
        // Step 1: Validate lat/lng
        $validatedData = $request->validated();

        // Step 2: Get the bounding box based on lat/lng
        $boundingBox = $searchService->getBoundingBox($validatedData['lat'], $validatedData['lng'], 5);

        // Step 3: Fetch and rank park-and-ride locations and parking spaces
        $parkingSpaces = $searchService->searchParkingSpaces($boundingBox)->toArray(); // Convert to array
        $rankedParkingSpaces = $parkingSpaceGateway->rank($parkingSpaces); // Now pass array for ranking 
    
        $parkAndRide = $searchService->searchParkAndRide($boundingBox)->toArray(); // Convert to array
        $rankedParkAndRide = $parkAndRideGateway->rank($parkAndRide); // Now pass array for ranking 
    
        // Step 4: Combine results and return them ranked (ParkAndRide always ranked higher)
        $resultArray = array_merge($rankedParkAndRide, $rankedParkingSpaces);
    
        // Step 5: Return the result as a resource collection
        return Location::collection(collect($resultArray));
    } 

    public function details(ValidateLocationRequest $request, SearchService $searchService)
    {
        // Step 1: Validate lat/lng
        $validatedData = $request->validated();

        // Step 2: Get the bounding box based on lat/lng
        $boundingBox = $searchService->getBoundingBox($validatedData['lat'], $validatedData['lng'], 5);

        // Step 3: Fetch and rank park-and-ride locations and parking spaces
        $parkingSpaces = $searchService->searchParkingSpaces($boundingBox);
        $parkAndRide = $searchService->searchParkAndRide($boundingBox);

        // Step 4: Combine results and return them ranked (ParkAndRide always ranked higher)
        $formatted = $this->formatLocations(array_merge($parkAndRide->toArray(), $parkingSpaces->toArray()));

        // Step 5: Return the result as a JSON response
        return response()->json($formatted);
    }

    private function formatLocations(array $locations)
    {
        return collect($locations)->map(function ($location) {
            if (array_key_exists('no_of_spaces', $location)) {
                // Parking space
                return [
                    'description' => "Parking space with {$location['no_of_spaces']} bays: {$location['space_details']}",
                    'location_name' => "{$location['street_name']}, {$location['city']}",
                ];
            } else {
                // Park and Ride
                return [
                    'description' => "Park and Ride to {$location['attraction_name']}. (approx {$location['minutes_to_destination']} minutes to destination)",
                    'location_name' => $location['location_description'],
                ];
            }    
        })->toArray();
    }
}