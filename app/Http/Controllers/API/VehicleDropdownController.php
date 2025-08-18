<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;

class VehicleDropdownController extends Controller
{
    use ApiResponseTrait;

    public function makes()
    {
        return $this->apiResponse('Vehicle makes fetched', [
            'makes' => DB::table('vehicle_makes')->where('status', 'active')->get()
        ]);
    }

    public function models()
    {
        return $this->apiResponse('Vehicle models fetched', [
            'models' => DB::table('vehicle_models')->where('status', 'active')->get()
        ]);
    }

    public function years()
    {
        return $this->apiResponse('Vehicle years fetched', [
            'years' => DB::table('vehicle_years')->where('status', 'active')->get()
        ]);
    }

    public function mileages()
    {
        return $this->apiResponse('Vehicle mileages fetched', [
            'mileages' => DB::table('vehicle_mileages')->where('status', 'active')->get()
        ]);
    }

    public function fuelTypes()
    {
        return $this->apiResponse('Fuel types fetched', [
            'fuel_types' => DB::table('vehicle_fuel_types')->where('status', 'active')->get()
        ]);
    }

    public function transmissionTypes()
    {
        return $this->apiResponse('Transmission types fetched', [
            'transmission_types' => DB::table('vehicle_transmission_types')->where('status', 'active')->get()
        ]);
    }

    public function states()
    {
        return $this->apiResponse('States fetched', [
            'states' => DB::table('vehicle_states')->where('status', 'active')->get()
        ]);
    }

    public function cities(Request $request)
    {
        $request->validate(['state_id' => 'required|integer']);

        return $this->apiResponse('Cities fetched by state', [
            'cities' => DB::table('vehicle_cities')
                ->where('status', 'active')
                ->where('state_id', $request->state_id)
                ->get()
        ]);
    }

    public function registrationStatuses()
    {
        return $this->apiResponse('Registration statuses fetched', [
            'statuses' => DB::table('vehicle_registration_statuses')->where('status', 'active')->get()
        ]);
    }

    public function engineModifications()
    {
        return $this->apiResponse('Engine modifications fetched', [
            'engine_modifications' => DB::table('vehicle_engine_modifications')->where('status', 'active')->get()
        ]);
    }

    public function exhaustSystems()
    {
        return $this->apiResponse('Exhaust systems fetched', [
            'exhaust_systems' => DB::table('vehicle_exhaust_systems')->where('status', 'active')->get()
        ]);
    }

    public function suspensions()
    {
        return $this->apiResponse('Suspensions fetched', [
            'suspensions' => DB::table('vehicle_suspensions')->where('status', 'active')->get()
        ]);
    }

    public function wheelsTires()
    {
        return $this->apiResponse('Wheels & tires fetched', [
            'wheels_tires' => DB::table('vehicle_wheels_tires')->where('status', 'active')->get()
        ]);
    }

    public function brakes()
    {
        return $this->apiResponse('Brakes fetched', [
            'brakes' => DB::table('vehicle_brakes')->where('status', 'active')->get()
        ]);
    }

    public function bodyKits()
    {
        return $this->apiResponse('Body kits fetched', [
            'body_kits' => DB::table('vehicle_body_kits')->where('status', 'active')->get()
        ]);
    }

    public function interiorUpgrades()
    {
        return $this->apiResponse('Interior upgrades fetched', [
            'interior_upgrades' => DB::table('vehicle_interior_upgrades')->where('status', 'active')->get()
        ]);
    }

    public function performanceTunings()
    {
        return $this->apiResponse('Performance tunings fetched', [
            'performance_tunings' => DB::table('vehicle_performance_tunings')->where('status', 'active')->get()
        ]);
    }

    public function electronics()
    {
        return $this->apiResponse('Electronics fetched', [
            'electronics' => DB::table('vehicle_electronics')->where('status', 'active')->get()
        ]);
    }

    public function interiorExteriors()
    {
        return $this->apiResponse('Interior & exterior fetched', [
            'interior_exteriors' => DB::table('vehicle_interior_exteriors')->where('status', 'active')->get()
        ]);
    }
    public function getAllDropdowns()
    {
        return $this->apiResponse('All dropdowns fetched', [
            'makes' => DB::table('vehicle_makes')->where('status', 'active')->get(),
            'models' => DB::table('vehicle_models')->where('status', 'active')->get(),
            'years' => DB::table('vehicle_years')->where('status', 'active')->get(),
            'mileages' => DB::table('vehicle_mileages')->where('status', 'active')->get(),
            'fuel_types' => DB::table('vehicle_fuel_types')->where('status', 'active')->get(),
            'transmission_types' => DB::table('vehicle_transmission_types')->where('status', 'active')->get(),
            'states' => DB::table('vehicle_states')->where('status', 'active')->get(),
            'cities' => DB::table('vehicle_cities')->where('status', 'active')->get(),
            'registration_statuses' => DB::table('vehicle_registration_statuses')->where('status', 'active')->get(),
            'engine_modifications' => DB::table('vehicle_engine_modifications')->where('status', 'active')->get(),
            'exhaust_systems' => DB::table('vehicle_exhaust_systems')->where('status', 'active')->get(),
            'suspensions' => DB::table('vehicle_suspensions')->where('status', 'active')->get(),
            'wheels_tires' => DB::table('vehicle_wheels_tires')->where('status', 'active')->get(),
            'brakes' => DB::table('vehicle_brakes')->where('status', 'active')->get(),
            'body_kits' => DB::table('vehicle_body_kits')->where('status', 'active')->get(),
            'interior_upgrades' => DB::table('vehicle_interior_upgrades')->where('status', 'active')->get(),
            'performance_tunings' => DB::table('vehicle_performance_tunings')->where('status', 'active')->get(),
            'electronics' => DB::table('vehicle_electronics')->where('status', 'active')->get(),
            'interior_exteriors' => DB::table('vehicle_interior_exteriors')->where('status', 'active')->get(),
            'categories' => DB::table('categories')->where('is_active', true)->get(),
            'sub_categories' => DB::table('sub_categories')->where('is_active', true)->get()
        ]);
    }

}