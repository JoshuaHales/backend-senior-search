<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ParkingSpace
 *
 * @property int $id
 * @property string $name
 * @property float $lat
 * @property float $lng
 * @property string $space_details
 * @property string $city
 * @property string $street_name
 * @property int $no_of_spaces
 * @property-read User $owner
 */
class ParkingSpace extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
