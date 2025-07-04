<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VehicleAd;
use App\Models\VehicleAttachment;
use Illuminate\Http\Request;

class VehicleAdController extends Controller
{
    public function index()
    {
        return VehicleAd::with('attachments')->where('user_id', auth()->id())->get();
    }

    public function publicVehicleAds(Request $request)
    {
        $query = VehicleAd::with(['attachments', 'user:id,name,profile_image'])
            ->where('status', 'active');

        // Apply optional filters
        if ($request->filled('make')) {
            $query->where('make', $request->make);
        }

        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->filled('transmission_type')) {
            $query->where('transmission_type', $request->transmission_type);
        }

        $vehicles = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json($vehicles);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'make' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'mileage' => 'required|integer',
            'fuel_type' => 'required|string',
            'transmission_type' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'registration_status' => 'required|string',
            'has_modification' => 'boolean',
            'engine_modification' => 'nullable|string',
            'exhaust_system' => 'nullable|string',
            'suspension' => 'nullable|string',
            'wheels_tires' => 'nullable|string',
            'brakes' => 'nullable|string',
            'body_kit' => 'nullable|string',
            'interior_upgrade' => 'nullable|string',
            'performance_tuning' => 'nullable|string',
            'electronics_infotainment' => 'nullable|string',
            'interior_exterior' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'is_featured' => 'boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'url',
        ]);

        $data['user_id'] = auth()->id();

        $vehicle = VehicleAd::create($data);

        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $url) {
                $relativePath = str_replace(asset('storage') . '/', '', $url);

                $vehicle->attachments()->create([
                    'file_path' => $relativePath
                ]);
            }
        }

        return response()->json([
            'message' => 'Vehicle created',
            'vehicle' => $vehicle->load('attachments')
        ], 201);
    }

    public function show($id)
    {
        $vehicle = VehicleAd::with('attachments')->findOrFail($id);
        abort_if($vehicle->user_id !== auth()->id(), 403);
        return response()->json($vehicle);
    }

    // public function update(Request $request, $id)
    // {
    //     $vehicle = VehicleAd::findOrFail($id);
    //     abort_if($vehicle->user_id !== auth()->id(), 403);

    //     $vehicle->update($request->all());
    //     return response()->json($vehicle);
    // }
    public function update(Request $request, $id)
    {
        $vehicle = VehicleAd::findOrFail($id);


        $validated = $request->validate([
            'make' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|numeric',
            'mileage' => 'required|numeric',
            'fuel_type' => 'required|string',
            'transmission_type' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'registration_status' => 'required|string',
            'price' => 'required|numeric',
            'is_featured' => 'boolean',

            // Modifications
            'has_modification' => 'required|boolean',
            'engine_modification' => 'nullable|string',
            'exhaust_system' => 'nullable|string',
            'suspension' => 'nullable|string',
            'wheels_tires' => 'nullable|string',
            'brakes' => 'nullable|string',
            'body_kit' => 'nullable|string',
            'interior_upgrade' => 'nullable|string',
            'performance_tuning' => 'nullable|string',
            'electronics_infotainment' => 'nullable|string',
            'interior_exterior' => 'nullable|string',
            'description' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'url',
        ]);

        // if (!empty($validated['attachments'])) {
        //     foreach ($validated['attachments'] as $url) {
        //         $relativePath = str_replace(asset('storage') . '/', '', $url);

        //         $vehicle->attachments()->create([
        //             'file_path' => $relativePath
        //         ]);
        //     }
        // }

        $vehicle->update($validated);

        return response()->json([
            'message' => 'Vehicle ad updated successfully',
            'vehicle' => $vehicle
        ]);
    }


    public function destroy($id)
    {
        $vehicle = VehicleAd::findOrFail($id);
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $vehicle->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function changeStatus($id)
    {
        $vehicle = VehicleAd::findOrFail($id);
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $vehicle->status = $vehicle->status === 'active' ? 'inactive' : 'active';
        $vehicle->save();

        return response()->json(['status' => $vehicle->status]);
    }

    public function uploadTempAttachment(Request $request)
    {
        $request->validate([
            'attachment' => 'required|file|mimes:jpg,jpeg,png,mp4,pdf|max:5120'
        ]);

        $path = $request->file('attachment')->store('vehicle_attachments', 'public');

        return response()->json([
            'message' => 'Attachment uploaded',
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

        return response()->json(['message' => 'Attachment deleted']);
    }


}
