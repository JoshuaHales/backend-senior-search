<?php
/** DO NOT EDIT */

namespace App\ThirdParty;

use GuzzleHttp\Psr7\Response;

class ParkingSpaceHttpService
{
    //timeout in ms
    public function getRanking(string $json, $timeout = null) {
        $executionTime = rand(1,5000);

        if ($timeout && $timeout < $executionTime) {
            throw new TimeoutException();
        }

        $ids = collect(json_decode($json, true));

        return new Response(200, [], $ids->sort());
    }
}
