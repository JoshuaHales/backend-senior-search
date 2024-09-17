<?php

namespace App\Gateways;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\ThirdParty\TimeoutException;
use App\ThirdParty\ParkAndRide\ParkAndRideSDK;
use App\ThirdParty\ParkAndRide\RankingRequest;

class ParkAndRideRankerGateway
{
    private $parkAndRide;

    public function __construct(ParkAndRideSDK $parkAndRide)
    {
        $this->parkAndRide = $parkAndRide;
    }

    public function rank(array $items) {
        // Step 1: Map the items by ID to keep them indexed
        $keyedItems = [];
        foreach ($items as $item) {
            $keyedItems[$item['id']] = $item;
        }

        try {
            // Step 2: Convert array keys (IDs) to a collection and pass to RankingRequest
            $ids = collect(array_keys($keyedItems)); 

            // Step 3: Get the ranked response from the third-party SDK
            $rankedResponse = $this->parkAndRide
                ->getRankingResponse(new RankingRequest($ids))  // Pass collection to RankingRequest
                ->getResult();

            Log::info('ParkAndRide ranking: ' . json_encode($rankedResponse));

            // Step 4: Sort the ranked response by the 'rank' field
            $arr = array_column($rankedResponse, 'rank');
            array_multisort($arr, SORT_ASC, $rankedResponse);

            // Step 5: Extract the ranked IDs and map them back to the original items
            $ranking = array_column($rankedResponse, 'park_and_ride_id');

            $rankedItems = [];
            foreach ($ranking as $rank) {
                $rankedItems[] = $keyedItems[$rank];
            }

            return $rankedItems;
        } catch (TimeoutException $e) {
            // Log the timeout exception
            Log::error('Park and Ride ranking service timed out: ' . $e->getMessage());

            // Return the unranked items as fallback
            return $items;
        }
    }
}