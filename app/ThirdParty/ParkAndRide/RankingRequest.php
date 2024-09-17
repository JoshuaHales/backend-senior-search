<?php
/** DO NOT EDIT */

namespace App\ThirdParty\ParkAndRide;

class RankingRequest
{
    private $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
