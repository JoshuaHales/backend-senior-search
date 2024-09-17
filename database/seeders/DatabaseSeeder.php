<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ParkAndRide;
use App\Models\ParkingSpace;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create some User records
        $owner1 = User::factory()->create(['name' => 'bob']);
        $owner2 = User::factory()->create(['name' => 'phil']);
        $owner3 = User::factory()->create(['name' => 'matt']);

        // Create ParkAndRide records using the factory
        ParkAndRide::factory()->create([
            'name' => 'Park n Ride 1',
            'lat' => 0.1,
            'lng' => 0.1,
            'user_id' => $owner1->id,
            'attraction_name' => 'disneyland',
            'location_description' => 'Main Street',
            'minutes_to_destination' => 10,
        ]);

        ParkAndRide::factory()->create([
            'name' => 'Park n Ride 3',
            'lat' => 0.1,
            'lng' => 0.1,
            'user_id' => $owner2->id,
            'attraction_name' => 'disneyland',
            'location_description' => 'Broadway',
            'minutes_to_destination' => 15,
        ]);

        // Create ParkingSpace records using the factory
        ParkingSpace::factory()->create([
            'name' => 'Parking Space 1',
            'lat' => 0.1,
            'lng' => 0.1,
            'user_id' => $owner3->id,
            'no_of_spaces' => 3,
            'street_name' => 'Oxford Street',
            'city' => 'London',
            'space_details' => 'Driveway off street',
        ]);
    }
}