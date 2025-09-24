<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\InspectorAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class InspectorAvailabilityController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $availabilities = InspectorAvailability::where('user_id', Auth::id())->get();
        return $this->apiResponse('Inspector availabilities fetched successfully.', $availabilities);
    }

    public function store(Request $request)
{
    $request->validate([
        'day' => 'required|in:mon,tue,wed,thu,fri,sat,sun',
        'is_available' => 'required|boolean',
        'start_time' => 'nullable|date_format:H:i',
        'end_time' => 'nullable|date_format:H:i|after:start_time',
    ]);

    // First find or create record
    $availability = InspectorAvailability::firstOrNew([
        'user_id' => Auth::id(),
        'day' => $request->day,
    ]);

    // Always update is_available
    $availability->is_available = $request->is_available;

    // Update times only if available = true AND values provided
    if ($request->is_available) {
        if ($request->filled('start_time')) {
            $availability->start_time = $request->start_time;
        }
        if ($request->filled('end_time')) {
            $availability->end_time = $request->end_time;
        }
    }

    $availability->save();

    return $this->apiResponse('Availability updated successfully.', $availability);
}


    public function destroy($id)
    {
        $availability = InspectorAvailability::where('user_id', Auth::id())->findOrFail($id);
        $availability->delete();

        return $this->apiResponse('Availability deleted successfully.', null);
    }
}
