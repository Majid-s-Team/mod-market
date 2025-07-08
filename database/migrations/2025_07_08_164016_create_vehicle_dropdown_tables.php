<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleDropdownTables extends Migration
{
    public function up()
    {
        $tables = [
            'vehicle_makes',
            'vehicle_models',
            'vehicle_years',
            'vehicle_mileages',
            'vehicle_fuel_types',
            'vehicle_transmission_types',
            'vehicle_cities',
            'vehicle_states',
            'vehicle_registration_statuses',
            'vehicle_engine_modifications',
            'vehicle_exhaust_systems',
            'vehicle_suspensions',
            'vehicle_wheels_tires',
            'vehicle_brakes',
            'vehicle_body_kits',
            'vehicle_interior_upgrades',
            'vehicle_performance_tunings',
            'vehicle_electronics',
            'vehicle_interior_exteriors',
        ];

        foreach ($tables as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        $tables = [
            'vehicle_makes',
            'vehicle_models',
            'vehicle_years',
            'vehicle_mileages',
            'vehicle_fuel_types',
            'vehicle_transmission_types',
            'vehicle_cities',
            'vehicle_states',
            'vehicle_registration_statuses',
            'vehicle_engine_modifications',
            'vehicle_exhaust_systems',
            'vehicle_suspensions',
            'vehicle_wheels_tires',
            'vehicle_brakes',
            'vehicle_body_kits',
            'vehicle_interior_upgrades',
            'vehicle_performance_tunings',
            'vehicle_electronics',
            'vehicle_interior_exteriors',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
}
