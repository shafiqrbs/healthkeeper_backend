<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Hospital\App\Models\PatientModel;

class PatientModelFactory extends Factory
{
    protected $model = PatientModel::class;

    public function definition()
    {
        return [
            'domain_id'           => 1,
            'customer_unique_key' => Str::uuid(),
            'name'                => $this->faker->name,
            'mobile'              => '017' . $this->faker->numberBetween(10000000, 99999999),
            'address'             => $this->faker->address,
            'dob'                 => $this->faker->date(),
            'age'                 => $this->faker->numberBetween(1, 90),
            'gender'              => $this->faker->randomElement(['male', 'female']),
            'country_id'          => 19,
            'status'              => true,
        ];
    }
}
