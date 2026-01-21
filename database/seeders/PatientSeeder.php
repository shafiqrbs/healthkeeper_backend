<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Hospital\App\Models\PatientModel;

class PatientSeeder extends Seeder
{
    public function run()
    {
        // Insert 20 random patients
        PatientModel::factory()->count(20)->create();

        // Insert one fixed patient (important for testing)
        PatientModel::create([
            'domain_id'           => 1,
            'customer_unique_key' => 'TEST-PATIENT-001',
            'name'                => 'Test Patient',
            'mobile'              => '01711111111',
            'address'             => 'Dhaka',
            'age'                 => 35,
            'gender'              => 'male',
            'country_id'          => 19,
            'status'              => true,
        ]);
    }
}
