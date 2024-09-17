<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\User;
use App\ParkingSpace;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParkingSpaceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ParkingSpace::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'user_id' => User::factory()->create(),
            'space_details' => $this->faker->sentence,
            'city' => $this->faker->word,
            'street_name' => $this->faker->word,
            'no_of_spaces' => $this->faker->randomNumber(),
        ];
    }
}
