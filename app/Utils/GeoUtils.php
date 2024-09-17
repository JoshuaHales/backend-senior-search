<?php

namespace App\Utils;

class GeoUtils
{
    const WGS84_A = 6378137.0; // Major semiaxis
    const WGS84_B = 6356752.3; // Minor semiaxis

    /**
     * Convert degrees to radians.
     *
     * @param  float $degrees
     * @return float
     */
    public static function degreesToRadians(float $degrees): float
    {
        return pi() * $degrees / 180.0;
    }

    /**
     * Convert radians to degrees.
     *
     * @param  float $radians
     * @return float
     */
    public static function radiansToDegrees(float $radians): float
    {
        return 180.0 * $radians / pi();
    }

    /**
     * Earth radius at a given latitude, according to the WGS-84 ellipsoid [m]
     *
     * @param  float $lat
     * @return float
     */
    public static function earthRadiusAtLatitude(float $lat): float
    {
        $An = self::WGS84_A * self::WGS84_A * cos($lat);
        $Bn = self::WGS84_B * self::WGS84_B * sin($lat);
        $Ad = self::WGS84_A * cos($lat);
        $Bd = self::WGS84_B * sin($lat);
        return sqrt(($An * $An + $Bn * $Bn) / ($Ad * $Ad + $Bd * $Bd));
    }
}