<?php

namespace App;

class SearchService
{
    const WGS84_A = 6378137.0; // Major semiaxis
    const WGS84_B = 6356752.3; // Major semiaxis

    public function searchParkingSpaces($boundingBox)
    {
        $results = [];
        ParkingSpace::whereBetween('lat', [$boundingBox['se_lat'], $boundingBox['nw_lat']])
            ->whereBetween('lng', [$boundingBox['nw_lng'], $boundingBox['se_lng']])
            ->get()
            ->each(function ($model) use (&$results) {
                $results[$model->id] = $model;
            });
        return $results;
    }

    public function searchParkAndRide($boundingBox)
    {
        $results = [];
        ParkAndRide::whereBetween('lat', [$boundingBox['se_lat'], $boundingBox['nw_lat']])
            ->whereBetween('lng', [$boundingBox['nw_lng'], $boundingBox['se_lng']])
            ->get()
            ->each(function ($model) use (&$results) {
                $results[$model->id] = $model;
            });
        return $results;
    }

    /********************* Only edit below at part 4) *******************************/

    public function getBoundingBox($lat, $lng, int $radius)
    {
        $lat = self::degrees2Radians($lat);
        $lng = self::degrees2Radians($lng);
        $halfSide = 1000 * $radius;

        $radius = self::WGS84EarthRadius($lat);
        $pRadius = $radius * cos($lat);

        return [
            'se_lat' => self::rad2deg($lat - $halfSide/$radius),
            'nw_lat' => self::rad2deg($lat + $halfSide/$radius),
            'nw_lng' => self::rad2deg($lng - $halfSide/$pRadius),
            'se_lng' => self::rad2deg($lng + $halfSide/$pRadius),
        ];
    }

    /**
     * Convert degrees to radians.
     *
     * @param  $degrees
     * @return float
     */
    public static function degrees2Radians($degrees)
    {
        return pi() * $degrees / 180.0;
    }

    /**
     * Convert radians to degrees.
     *
     * @param  $radians
     * @return float
     */
    public static function rad2deg($radians)
    {
        return 180.0 * $radians / pi();
    }

    /**
     * Earth radius at a given latitude, according to the WGS-84 ellipsoid [m]
     *
     * @param  $lat
     * @return float
     */
    public static function WGS84EarthRadius($lat)
    {
        $An = self::WGS84_A * self::WGS84_A * cos($lat);
        $Bn = self::WGS84_B * self::WGS84_B * sin($lat);
        $Ad = self::WGS84_A * cos($lat);
        $Bd = self::WGS84_B * sin($lat);
        return sqrt(($An * $An + $Bn * $Bn) / ($Ad * $Ad + $Bd * $Bd));
    }
}
