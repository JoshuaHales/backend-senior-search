<?php
/** DO NOT EDIT */

namespace App\ThirdParty\ParkAndRide;

use App\ThirdParty\TimeoutException;

class RankingResponse
{
    private $request;
    private $timeout;

    public function __construct(RankingRequest $request, $timeout = null)
    {
        $this->request = $request;
        $this->timeout = $timeout;
    }

    public function getResult(): array
    {
        $executionTime = rand(1,5000);

        if ($this->timeout && $this->timeout < $executionTime) {
            throw new TimeoutException();
        }

        if (!env('PRODUCTION_KEY')) {
            throw new BadProductionKey();
        };

        sleep(rand(10,60));

        return $this->request->getIds()->sort()->map(function ($val, $key) { // The sort does not work by default as ->getIds() by default returns an array?
            return [
                'park_and_ride_id' => $val,
                'rank' => $key
            ];
        })->shuffle()->values()->toArray();

        // return collect($this->request->getIds()) // Wrap the array in a collection
        //     ->sort() // Now sort the collection
        //     ->map(function ($val, $key) {
        //         return [
        //             'park_and_ride_id' => $val,
        //             'rank' => $key
        //         ];
        //     })->shuffle()->values()->toArray(); 
    }
}
