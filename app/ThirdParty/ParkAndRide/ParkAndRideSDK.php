<?php
/** DO NOT EDIT */

namespace App\ThirdParty\ParkAndRide;

class ParkAndRideSDK
{
    //timeout in ms
    public function getRankingResponse(RankingRequest $request, $timeout = null) {
        return new RankingResponse($request, $timeout);
    }
}
