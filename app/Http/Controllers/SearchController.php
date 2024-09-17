<?php

namespace App\Http\Controllers;

use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\SearchService;

class SearchController extends Controller
{
    public function index(
        SearchService $searchService,
        ParkAndRideRankerGateway $parkAndRideGateway,
        ParkingSpaceRankerGateway $parkingSpaceGateway
    ) {
        // @todo Part 3) validate lat long
        $boundingBox = $searchService->getBoundingBox(request()->input('lat'), request()->input('lng'), 5);
        $parkingSpaces = $searchService->searchParkingSpaces($boundingBox);
        // @todo Part 2) rank parking spaces

        $parkAndRide = $searchService->searchParkAndRide($boundingBox);
        $rankedParkAndRide = $parkAndRideGateway->rank($parkAndRide);

        $resultArray = [];/*@todo Part 2)*/
        // @todo Part 3)  N+1 queries inside the resource transformer
        return \App\Http\Resources\Location::collection(collect($resultArray));
    }

    public function details(SearchService $searchService)
    {
        // @todo Part 3) validate lat long
        $boundingBox = $searchService->getBoundingBox(request()->input('lat'), request()->input('lng'), 5);
        $parkingSpaces = $searchService->searchParkingSpaces($boundingBox);
        $parkAndRide = $searchService->searchParkAndRide($boundingBox);

        return response()->json($this->formatLocations([])); /*@todo Part 1) */
    }

    private function formatLocations(array $things)
    {
        //@todo Part 1) format 'park and rides' and parking spaces for response
    }
}
