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
            // 'start_time' => 'nullable|required_if:is_available,true|date_format:H:i',
            // 'end_time' => 'nullable|required_if:is_available,true|date_format:H:i|after:start_time',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ]);

        $availability = InspectorAvailability::updateOrCreate(
            ['user_id' => Auth::id(), 'day' => $request->day],
            [
                'is_available' => $request->is_available,
                'start_time' => $request->is_available ? $request->start_time : null,
                'end_time' => $request->is_available ? $request->end_time : null
            ]
        );

        return $this->apiResponse('Availability updated successfully.', $availability);
    }

    public function destroy($id)
    {
        $availability = InspectorAvailability::where('user_id', Auth::id())->findOrFail($id);
        $availability->delete();

        return $this->apiResponse('Availability deleted successfully.', null);
    }
}
