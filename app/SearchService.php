<?php

namespace App;

use App\Utils\GeoUtils;
use App\Models\ParkAndRide;
use App\Models\ParkingSpace;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class SearchService
{
    /**
     * General method to search for locations within a bounding box.
     * 
     * @param  Model $modelClass
     * @param  array $boundingBox
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function searchByBoundingBox(Model $modelClass, array $boundingBox)
    {
        return $modelClass::with('owner')
            ->whereBetween('lat', [$boundingBox['se_lat'], $boundingBox['nw_lat']])
            ->whereBetween('lng', [$boundingBox['nw_lng'], $boundingBox['se_lng']])
            ->get();
    }

    /**
     * Search for ParkingSpaces within the bounding box.
     * 
     * @param array $boundingBox
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchParkingSpaces(array $boundingBox)
    {
        Log::info("Searching ParkingSpaces within bounding box: " . json_encode($boundingBox));
        return $this->searchByBoundingBox(new ParkingSpace, $boundingBox);
    }

    /**
     * Search for ParkAndRide within the bounding box.
     * 
     * @param array $boundingBox
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchParkAndRide(array $boundingBox)
    {
        Log::info("Searching ParkAndRide within bounding box: " . json_encode($boundingBox));
        return $this->searchByBoundingBox(new ParkAndRide, $boundingBox);
    }

    /********************* Only edit below at part 4) *******************************/

    /**
     * Get the bounding box for a location with a given radius.
     *
     * @param float $lat
     * @param float $lng
     * @param int $radius
     * @return array
     */
    public function getBoundingBox(float $lat, float $lng, int $radius): array
    {
        $lat = GeoUtils::degreesToRadians($lat);
        $lng = GeoUtils::degreesToRadians($lng);
        $halfSide = 1000 * $radius;

        $radius = GeoUtils::earthRadiusAtLatitude($lat);
        $pRadius = $radius * cos($lat);

        return $this->calculateBoundingBox($lat, $lng, $halfSide, $radius, $pRadius);
    }

    /**
     * Helper method to calculate the bounding box from latitude, longitude, and radius.
     *
     * @param float $lat
     * @param float $lng
     * @param float $halfSide
     * @param float $radius
     * @param float $pRadius
     * @return array
     */
    private function calculateBoundingBox($lat, $lng, $halfSide, $radius, $pRadius)
    {
        return [
            'se_lat' => GeoUtils::radiansToDegrees($lat - $halfSide / $radius),
            'nw_lat' => GeoUtils::radiansToDegrees($lat + $halfSide / $radius),
            'nw_lng' => GeoUtils::radiansToDegrees($lng - $halfSide / $pRadius),
            'se_lng' => GeoUtils::radiansToDegrees($lng + $halfSide / $pRadius),
        ];
    }
}