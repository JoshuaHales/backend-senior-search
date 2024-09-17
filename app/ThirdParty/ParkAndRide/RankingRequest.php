<?php
/** DO NOT EDIT */

namespace App\ThirdParty\ParkAndRide;

class RankingRequest
{
    private $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function getIds()
    {
        return $this->ids;
    }
}
