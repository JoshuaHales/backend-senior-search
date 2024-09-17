<?php

namespace App\Gateways;

use Illuminate\Support\Facades\Log;
use App\ThirdParty\TimeoutException;
use App\ThirdParty\ParkingSpaceHttpService;
use App\ThirdParty\ParkAndRide\RankingRequest;


class ParkingSpaceRankerGateway
{
    private $parkingSpaceService;

    public function __construct(ParkingSpaceHttpService $parkingSpaceService)
    {
        $this->parkingSpaceService = $parkingSpaceService;
    }

    public function rank($items)
    {
        // Step 1: Map the items by ID to keep them indexed
        $keyedItems = [];
        foreach ($items as $item) {
            $keyedItems[$item['id']] = $item;
        }

        try {
            // Step 2: Mock the ranking service
            $rankedResponse = $this->parkingSpaceService->getRanking(json_encode(array_keys($keyedItems)))->getBody()->getContents();
            $ranking = json_decode($rankedResponse);

            Log::info('ParkingSpaces ranking: ' . json_encode($ranking));

            // Step 3: Reorder the items based on the ranking
            $rankedItems = [];
            foreach ($ranking as $rank) {
                $rankedItems[] = $keyedItems[$rank];
            }

            return $rankedItems;
        } catch (TimeoutException $e) {
            // Log the timeout exception
            Log::error('Parking space ranking service timed out: ' . $e->getMessage());

            // Return the unranked items as fallback
            return $items;
        }
    }
}