<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVehicleAdsTableForeignDropdowns extends Migration
{
    public function up()
    {
        Schema::table('vehicle_ads', function (Blueprint $table) {
            $table->dropColumn([
                'make', 'model', 'year', 'mileage', 'fuel_type', 'transmission_type',
                'city', 'state', 'registration_status',
                'engine_modification', 'exhaust_system', 'suspension', 'wheels_tires',
                'brakes', 'body_kit', 'interior_upgrade', 'performance_tuning',
                'electronics', 
            ]);


            $table->foreignId('make_id')->nullable()->constrained('vehicle_makes')->nullOnDelete();
            $table->foreignId('model_id')->nullable()->constrained('vehicle_models')->nullOnDelete();
            $table->foreignId('year_id')->nullable()->constrained('vehicle_years')->nullOnDelete();
            $table->foreignId('mileage_id')->nullable()->constrained('vehicle_mileages')->nullOnDelete();
            $table->foreignId('fuel_type_id')->nullable()->constrained('vehicle_fuel_types')->nullOnDelete();
            $table->foreignId('transmission_type_id')->nullable()->constrained('vehicle_transmission_types')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('vehicle_cities')->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('vehicle_states')->nullOnDelete();
            $table->foreignId('registration_status_id')->nullable()->constrained('vehicle_registration_statuses')->nullOnDelete();


            $table->foreignId('engine_modification_id')->nullable()->constrained('vehicle_engine_modifications')->nullOnDelete();
            $table->foreignId('exhaust_system_id')->nullable()->constrained('vehicle_exhaust_systems')->nullOnDelete();
            $table->foreignId('suspension_id')->nullable()->constrained('vehicle_suspensions')->nullOnDelete();
            $table->foreignId('wheels_tires_id')->nullable()->constrained('vehicle_wheels_tires')->nullOnDelete();
            $table->foreignId('brakes_id')->nullable()->constrained('vehicle_brakes')->nullOnDelete();
            $table->foreignId('body_kit_id')->nullable()->constrained('vehicle_body_kits')->nullOnDelete();
            $table->foreignId('interior_upgrade_id')->nullable()->constrained('vehicle_interior_upgrades')->nullOnDelete();
            $table->foreignId('performance_tuning_id')->nullable()->constrained('vehicle_performance_tunings')->nullOnDelete();
            $table->foreignId('electronics_id')->nullable()->constrained('vehicle_electronics')->nullOnDelete();
            $table->foreignId('interior_exterior_id')->nullable()->constrained('vehicle_interior_exteriors')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('vehicle_ads', function (Blueprint $table) {
            $table->dropForeign([
                'make_id', 'model_id', 'year_id', 'mileage_id', 'fuel_type_id',
                'transmission_type_id', 'city_id', 'state_id', 'registration_status_id',
                'engine_modification_id', 'exhaust_system_id', 'suspension_id', 'wheels_tires_id',
                'brakes_id', 'body_kit_id', 'interior_upgrade_id', 'performance_tuning_id',
                'electronics_id', 'interior_exterior_id'
            ]);

            $table->dropColumn([
                'make_id', 'model_id', 'year_id', 'mileage_id', 'fuel_type_id',
                'transmission_type_id', 'city_id', 'state_id', 'registration_status_id',
                'engine_modification_id', 'exhaust_system_id', 'suspension_id', 'wheels_tires_id',
                'brakes_id', 'body_kit_id', 'interior_upgrade_id', 'performance_tuning_id',
                'electronics_id', 'interior_exterior_id'
            ]);
        });
    }
}
