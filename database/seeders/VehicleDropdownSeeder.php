<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleDropdownSeeder extends Seeder
{
    public function run(): void
    {
        $dropdownData = [
            'vehicle_makes' => ['Toyota', 'Honda', 'Suzuki'],
            'vehicle_models' => ['Corolla', 'Civic', 'Alto'],
            'vehicle_years' => ['2020', '2021', '2022'],
            'vehicle_mileages' => ['0-10k', '10k-50k', '50k+'],
            'vehicle_fuel_types' => ['Petrol', 'Diesel', 'Hybrid', 'Electric'],
            'vehicle_transmission_types' => ['Automatic', 'Manual'],
            'vehicle_cities' => ['Karachi', 'Lahore', 'Islamabad'],
            'vehicle_states' => ['Sindh', 'Punjab', 'KPK'],
            'vehicle_registration_statuses' => ['Registered', 'Unregistered'],

            'vehicle_engine_modifications' => ['Turbocharged', 'Supercharged'],
            'vehicle_exhaust_systems' => ['Custom Muffler', 'Straight Pipe'],
            'vehicle_suspensions' => ['Sport Suspension', 'Air Suspension'],
            'vehicle_wheels_tires' => ['Alloy Wheels', 'Off-Road Tires'],
            'vehicle_brakes' => ['ABS', 'Disc Brakes'],
            'vehicle_body_kits' => ['Sport Body Kit', 'Wide Body Kit'],
            'vehicle_interior_upgrades' => ['Leather Seats', 'Ambient Lighting'],
            'vehicle_performance_tunings' => ['Stage 1 Tune', 'ECU Remap'],
            'vehicle_electronics' => ['Navigation System', 'Digital Cluster'],
            'vehicle_interior_exteriors' => ['Carbon Interior', 'Chrome Exterior'],
        ];

        foreach ($dropdownData as $table => $values) {
            foreach ($values as $name) {
                DB::table($table)->insert([
                    'name' => $name,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
