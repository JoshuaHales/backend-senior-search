<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Location extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => is_array($this->resource) ? $this->resource['id'] : $this->id,
            'name' => is_array($this->resource) ? $this->resource['name'] : $this->name,
            'lat' => is_array($this->resource) ? $this->resource['lat'] : $this->lat,
            'lng' => is_array($this->resource) ? $this->resource['lng'] : $this->lng,
            'owner' => [
                'name' => is_array($this->resource) ? $this->resource['owner']['name'] : $this->owner->name,
            ],
        ];
    }
}