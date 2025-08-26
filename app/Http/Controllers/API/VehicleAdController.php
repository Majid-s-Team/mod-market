<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleAd;
use App\Models\VehicleAttachment;
use App\Models\VehicleCity;
use App\Models\VehicleState;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Illuminate\Auth\AuthenticationException;


class VehicleAdController extends Controller
{
    use ApiResponseTrait;

public function index(Request $request)
{
    $perPage = $request->get('per_page', 10);
    $authUserId = null;

    if ($request->bearerToken()) {
        try {
            // authenticate user through bearer
            $user = auth('api')->user();
            if (!$user) {
             // Token is invalid or expired (returned null)
             throw new AuthenticationException('Unauthenticated. Invalid or expired token.');
            }
                $authUserId = $user->id;

        } catch (AuthenticationException $e) {
            //token un-authorized exception
            return response()->json([
                'message' => $e-> getMessage()], 401);
        }
    }
    $query = VehicleAd::with([
        'attachments',
        'make:id,name',
        'model:id,name',
        'year:id,name',
        'mileage:id,name',
        'fuelType:id,name',
        'transmissionType:id,name',
        'registrationStatus:id,name',
        'engineModification:id,name',
        'exhaustSystem:id,name',
        'suspension:id,name',
        'wheelsTires:id,name',
        'brakes:id,name',
        'bodyKit:id,name',
        'interiorUpgrade:id,name',
        'performanceTuning:id,name',
        'electronics:id,name',
        'interiorExterior:id,name',
        'city:id,name',
        'state:id,name',
        'category:id,name',
        'subCategory:id,name',
    ])->latest();

    if ($authUserId) {
        // Agar authenticated user hai to uske ads dikhao
        $query->where('user_id', $authUserId);
    } else {
        // Agar guest hai to sirf active public ads dikhao
        $query->where('status', 'active');
    }

    $ads = $query->paginate($perPage);

    return $this->apiPaginatedResponse('Vehicle ads fetched successfully', $ads);
}
    public function publicVehicleAds(Request $request)
    {
         if ($request->bearerToken()) {
        try {
            // authenticate user through bearer
            $user = auth('api')->user();
            if (!$user) {
             // Token is invalid or expired (returned null)
             throw new AuthenticationException('Unauthenticated. Invalid or expired token.');
            }
                $authUserId = $user->id;

        } catch (AuthenticationException $e) {
            //token un-authorized exception
            return response()->json([
                'message' => $e-> getMessage()], 401);
        }
    }


        $query = VehicleAd::with((new VehicleAd())->getAllRelations())
            ->where('status', 'active');


        if ($request->filled('id')) {
            $query->where('id', $request->id);
        } else {

            foreach ([
                'make_id',
                'model_id',
                'city_id',
                'state_id',
                'fuel_type_id',
                'transmission_type_id'
            ] as $filter) {
                if ($request->filled($filter)) {
                    $query->where($filter, $request->$filter);
                }
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }
        }

        $vehicles = $query->latest()->paginate($request->get('per_page', 10));

        return $this->apiPaginatedResponse('Public vehicle ads fetched', $vehicles, 200);
    }



    public function store(Request $request)
    {
        $data = $request->validate([
            'make_id' => 'required|exists:vehicle_makes,id',
            'model_id' => 'required|exists:vehicle_models,id',
            'year_id' => 'required|exists:vehicle_years,id',
            'mileage_id' => 'required|exists:vehicle_mileages,id',
            'fuel_type_id' => 'required|exists:vehicle_fuel_types,id',
            'transmission_type_id' => 'required|exists:vehicle_transmission_types,id',
            'city_id' => 'required|exists:vehicle_cities,id',
            'state_id' => 'required|exists:vehicle_states,id',
            'registration_status_id' => 'required|exists:vehicle_registration_statuses,id',
            'engine_modification_id' => 'nullable|exists:vehicle_engine_modifications,id',
            'is_modified' => 'boolean',
            'exhaust_system_id' => 'nullable|exists:vehicle_exhaust_systems,id',
            'suspension_id' => 'nullable|exists:vehicle_suspensions,id',
            'wheels_tires_id' => 'nullable|exists:vehicle_wheels_tires,id',
            'brakes_id' => 'nullable|exists:vehicle_brakes,id',
            'body_kit_id' => 'nullable|exists:vehicle_body_kits,id',
            'interior_upgrade_id' => 'nullable|exists:vehicle_interior_upgrades,id',
            'performance_tuning_id' => 'nullable|exists:vehicle_performance_tunings,id',
            'electronics_id' => 'nullable|exists:vehicle_electronics,id',
            'interior_exterior_id' => 'nullable|exists:vehicle_interior_exteriors,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'is_featured' => 'boolean',
            'attachments' => 'nullable|array',

            'attachments.*' => 'url'
        ]);

        $data['user_id'] = auth()->id();
        $vehicle = VehicleAd::create($data);

        // if (!empty($data['attachments'])) {
        //     foreach ($data['attachments'] as $url) {
        //         $relativePath = str_replace(asset('storage') . '/', '', $url);
        //         $vehicle->attachments()->create(['file_path' => $relativePath]);
        //     }
        // }

        if (!empty($data['attachments'])) {
    foreach ($data['attachments'] as $url) {
        $vehicle->attachments()->create([
            'file_path' => $url  // store full URL directly
        ]);
    }
}


        return $this->apiResponse('Vehicle ad created successfully', [
            'vehicle' => $vehicle->load('attachments')
        ], 201);
    }

    public function show($id)
    {
        // $vehicle = VehicleAd::with('attachments','category','subCategory')->findOrFail($id);
        $vehicle = VehicleAd::with((new VehicleAd())->getAllRelations())->findOrFail($id);

        abort_if($vehicle->user_id !== auth()->id(), 403);
        return $this->apiResponse('Vehicle ad fetched', ['vehicle' => $vehicle]);
    }

    public function update(Request $request, $id)
    {
        $vehicle = VehicleAd::findOrFail($id);
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'make_id' => 'required|exists:vehicle_makes,id',
            'model_id' => 'required|exists:vehicle_models,id',
            'year_id' => 'required|exists:vehicle_years,id',
            'mileage_id' => 'required|exists:vehicle_mileages,id',
            'fuel_type_id' => 'required|exists:vehicle_fuel_types,id',
            'transmission_type_id' => 'required|exists:vehicle_transmission_types,id',
            'city_id' => 'required|exists:vehicle_cities,id',
            'state_id' => 'required|exists:vehicle_states,id',
            'registration_status_id' => 'required|exists:vehicle_registration_statuses,id',
            'engine_modification_id' => 'nullable|exists:vehicle_engine_modifications,id',
            'is_modified' => 'boolean',
            'exhaust_system_id' => 'nullable|exists:vehicle_exhaust_systems,id',
            'suspension_id' => 'nullable|exists:vehicle_suspensions,id',
            'wheels_tires_id' => 'nullable|exists:vehicle_wheels_tires,id',
            'brakes_id' => 'nullable|exists:vehicle_brakes,id',
            'body_kit_id' => 'nullable|exists:vehicle_body_kits,id',
            'interior_upgrade_id' => 'nullable|exists:vehicle_interior_upgrades,id',
            'performance_tuning_id' => 'nullable|exists:vehicle_performance_tunings,id',
            'electronics_id' => 'nullable|exists:vehicle_electronics,id',
            'interior_exterior_id' => 'nullable|exists:vehicle_interior_exteriors,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'is_featured' => 'boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'url'
        ]);

        DB::beginTransaction();

        try {
            $vehicle->update($validated);

            // Attachments delete and insert
            if ($request->has('attachments')) {
                // Old attachments delete + files delete from storage
                foreach ($vehicle->attachments as $attachment) {
                    \Storage::disk('public')->delete($attachment->file_path);
                    $attachment->delete();
                }

                // New attachments insert
                foreach ($validated['attachments'] as $url) {
                    $relativePath = str_replace(asset('storage') . '/', '', $url);
                    $vehicle->attachments()->create(['file_path' => $relativePath]);
                }
            }

            DB::commit();

            return $this->apiResponse('Vehicle ad updated successfully', [
                'vehicle' => $vehicle->load('attachments')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiError('Failed to update vehicle ad', ['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $vehicle = VehicleAd::findOrFail($id);
        abort_if($vehicle->user_id !== auth()->id(), 403);
        $vehicle->delete();
        return $this->apiResponse('Vehicle ad deleted successfully');
    }

    public function changeStatus($id)
    {
        $vehicle = VehicleAd::findOrFail($id);
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $vehicle->status = $vehicle->status === 'active' ? 'inactive' : 'active';
        $vehicle->save();

        return $this->apiResponse('Vehicle status changed', ['status' => $vehicle->status]);
    }

    public function uploadTempAttachment(Request $request)
    {
        $request->validate([
            'attachment' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,pdf,doc,docx,xlsx,xls|max:51200'
        ]);

        $path = $request->file('attachment')->store('vehicle_attachments', 'public');

        return $this->apiResponse('Attachment uploaded successfully', [
            'url' => asset('storage/' . $path)
        ]);
    }

    public function deleteAttachment($vehicleId, $attachmentId)
    {
        $vehicle = VehicleAd::findOrFail($vehicleId);
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $attachment = $vehicle->attachments()->where('id', $attachmentId)->firstOrFail();
        \Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return $this->apiResponse('Attachment deleted successfully');
    }
}
