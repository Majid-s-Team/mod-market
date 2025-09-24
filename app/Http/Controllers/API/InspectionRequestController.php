<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\InspectionRequest;
use App\Models\User;
use App\Models\Card;
use App\Models\VehicleAd;
use App\Models\InspectionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use Illuminate\Validation\Rule;


class InspectionRequestController extends Controller
{
    use ApiResponseTrait;
// public function index(Request $request)
// {
//     $user = Auth::user();
//     $perPage = $request->get('per_page', 10);

//     // Get paginated inspection requests
//     $requests = InspectionRequest::with([
//         'user',
//         'city:id,name',
//         'state:id,name',
//         'vehicleAd' => function ($query) {
//             $query->with((new VehicleAd)->getAllRelations());
//         }
//     ])
//     ->where('user_id', $user->id)
//     ->orderByDesc('created_at')
//     ->paginate($perPage);

//     // For each request, check status in inspection_requests table
//     // If completed, get inspection_reports id
//     $requests->getCollection()->transform(function ($request) {
//         $request->completed_report_id = null;

//         if ($request->status === 'completed') {
//             // Get the report id from inspection_reports table
//             $report = InspectionReport::where('inspection_request_id', $request->id)
//                 ->first();

//             $request->completed_report_id = $report ? $report->id : null;
//         }

//         return $request;
//     });

//     return $this->apiPaginatedResponse('Inspection requests fetched.', $requests);
// }



public function index(Request $request)
{
    $user = Auth::user();
    $perPage = $request->get('per_page', 10);

    // Get paginated inspection requests
    $requests = InspectionRequest::with([
        'user',
        'city:id,name',
        'state:id,name',
        'vehicleAd' => function ($query) {
            $query->with((new VehicleAd)->getAllRelations());
        },
        'inspector' // 👈 Inspector relation eager load
    ])
    ->where('user_id', $user->id)
    ->orderByDesc('created_at')
    ->paginate($perPage);

    // Transform the response
    $requests->getCollection()->transform(function ($request) {
        $request->completed_report_id = null;

        // Completed report id
        if ($request->status === 'completed') {
            $report = InspectionReport::where('inspection_request_id', $request->id)->first();
            $request->completed_report_id = $report ? $report->id : null;
        }

        // Inspector object handle
        if ($request->type === 'vendor') {
            $request->inspector = $request->inspector; // return relation object
        } else {
            $request->inspector = null; // agar self ho
        }

        return $request;
    });

    return $this->apiPaginatedResponse('Inspection requests fetched.', $requests);
}



public function tokenSelf(Request $request)
{
    $user = Auth::user();
    $perPage = $request->get('per_page', 10);

    $requests = InspectionRequest::with([
            'user',               // requester user
            'city:id,name',
            'state:id,name',
            'vehicleAd' => function ($query) {
                $query->with((new VehicleAd)->getAllRelations())
                      ->with('user') // ad owner
                    ->with('tokenRequests');    // <-- tokenRequest relation bhi include karo

            }
        ])
        ->whereHas('vehicleAd', function ($q) use ($user) {
            // only those ads which belong to the auth user
            $q->where('user_id', $user->id);
        })
        ->orderByDesc('created_at')
        ->paginate($perPage);

    return $this->apiPaginatedResponse('Inspection requests sent to you fetched.', $requests);
}


public function updateRequestStatus(Request $request, $id)
{
    $user = Auth::user();

    // ✅ validate request
    $validated = $request->validate([
        'status' => 'required|in:accepted,rejected',
        'reason' => 'required_if:status,rejected|nullable|string|max:1000',
    ]);

    // ✅ find inspection request
    $inspectionRequest = InspectionRequest::with('vehicleAd')->findOrFail($id);

    // ✅ check ownership
    if ($inspectionRequest->vehicleAd->user_id !== $user->id) {
        return $this->apiError('You are not authorized to update this request.', [], 403);
    }

    // ✅ update status + reason (only if provided)
    $inspectionRequest->status = $validated['status'];
    $inspectionRequest->reasons = $validated['reason'] ?? null;
    $inspectionRequest->save();

    return $this->apiResponse('Inspection request status updated successfully.', [
        'inspection_request_id' => $inspectionRequest->id,
        'status' => $inspectionRequest->status,
        'reason' => $inspectionRequest->reasons,
    ]);
}



    /**
     * Store a new inspection request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(['self', 'vendor'])],
            'vehicle_ad_id' => 'required|exists:vehicle_ads,id',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'city_id' => 'required|exists:vehicle_cities,id',
            'state_id' => 'required|exists:vehicle_states,id',

            // Self inspection
            'inspection_date' => 'required_if:type,self|nullable|date',
            'inspection_time' => 'required_if:type,self|nullable|date_format:H:i',
            'want_test_drive' => 'nullable|boolean',

            // Vendor
            'inspector_id' => 'required_if:type,vendor|exists:users,id',
            'inspection_date_start' => 'required_if:type,vendor|nullable|date',
            'inspection_date_end' => 'required_if:type,vendor|nullable|date|after_or_equal:inspection_date_start',
            'inspection_time_start' => 'required_if:type,vendor|nullable|date_format:H:i',
            'inspection_time_end' => 'required_if:type,vendor|nullable|date_format:H:i|after_or_equal:inspection_time_start',
            'description' => 'nullable|string',
            'card_id' => 'required_if:type,vendor|exists:cards,id'
        ]);

        $data = $request->only([
            'vehicle_ad_id',
            'full_name',
            'phone_number',
            'city_id',
            'state_id',
            'inspection_date',
            'inspection_time',
            'inspection_date_start',
            'inspection_date_end',
            'inspection_time_start',
            'inspection_time_end',
            'want_test_drive',
            'description',
            'inspector_id',
            'type'
        ]);

        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';
        $data['payment_status'] = 'unpaid';

        if ($request->type === 'vendor') {
            $inspector = User::findOrFail($request->inspector_id);


            if (!$inspector->hasRole('inspector')) {
                return $this->apiError('Selected user is not a valid inspector.', [], 422);
            }

            $existing = InspectionRequest::where('user_id', Auth::id())
                ->where('inspector_id', $request->inspector_id)
                ->where('vehicle_ad_id', $request->vehicle_ad_id)
                ->where('status', 'pending')
                ->first();

            if ($existing) {
                return $this->apiError('You already have a pending request with this inspector for this vehicle.', [], 422);
            }


            $data['inspector_price'] = $inspector->service_rate;
            $card = Card::where('id', $request->card_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$card) {
                return $this->apiError('Invalid card selected.', [], 422);
            }

            $fakePaymentSuccess = true;
            $fakePaymentReference = 'TXN' . time();

            if (!$fakePaymentSuccess) {
                return $this->apiError('Payment failed.', [], 500);
            }

            $data['payment_status'] = 'paid';
            $data['payment_reference'] = $fakePaymentReference;
        } else {
            $data['payment_status'] = 'unpaid';
        }

        $inspection = InspectionRequest::create($data);

        return $this->apiResponse('Inspection request created.', $inspection);
    }

    /**
     * Show details of a single inspection request.
     */
    public function show($id)
    {
        $inspection = InspectionRequest::with([
            'user:id,name',
            'inspector:id,name,service_rate',
            'vehicleAd:id,make_id,model_id',
            'city:id,name',
            'state:id,name',
        ])->findOrFail($id);

        return $this->apiResponse('Inspection request details.', $inspection);
    }

    /**
     * Update status or payment of inspection request (for admins/inspectors).
     */

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'accepted', 'rejected', 'completed'])],
            'payment_status' => ['nullable', Rule::in(['paid', 'unpaid'])],
            'type' => ['nullable', Rule::in(['self', 'vendor'])],
            'vehicle_ad_id' => 'nullable|exists:vehicle_ads,id',
            'full_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'city_id' => 'nullable|exists:vehicle_cities,id',
            'state_id' => 'nullable|exists:vehicle_states,id',

            // Self
            'inspection_date' => 'nullable|date',
            'inspection_time' => 'nullable|date_format:H:i',
            'want_test_drive' => 'nullable|boolean',

            // Vendor
            'inspector_id' => 'nullable|exists:users,id',
            'inspection_date_start' => 'nullable|date',
            'inspection_date_end' => 'nullable|date|after_or_equal:inspection_date_start',
            'inspection_time_start' => 'nullable|date_format:H:i',
            'inspection_time_end' => 'nullable|date_format:H:i|after_or_equal:inspection_time_start',
            'description' => 'nullable|string',
        ]);

        $inspection = InspectionRequest::findOrFail($id);


        $inspection->fill($request->only([
            'vehicle_ad_id',
            'full_name',
            'phone_number',
            'city_id',
            'state_id',
            'inspection_date',
            'inspection_time',
            'inspection_date_start',
            'inspection_date_end',
            'inspection_time_start',
            'inspection_time_end',
            'want_test_drive',
            'description',
            'inspector_id',
            'type',
            'status',
            'payment_status'
        ]));


        if ($request->type === 'vendor' && $request->inspector_id) {
            $inspector = User::findOrFail($request->inspector_id);
            if (!$inspector->hasRole('inspector')) {
                return $this->apiError('Selected user is not a valid inspector.', [], 422);
            }
            $inspection->inspector_price = $inspector->service_rate;
        }

        $inspection->save();

        return $this->apiResponse('Inspection request updated.', $inspection);
    }

    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'status' => ['nullable', Rule::in(['pending', 'accepted', 'rejected', 'completed'])],
    //         'payment_status' => ['nullable', Rule::in(['paid', 'unpaid'])],
    //     ]);

    //     $inspection = InspectionRequest::findOrFail($id);

    //     if ($request->has('status')) {
    //         $inspection->status = $request->status;
    //     }

    //     if ($request->has('payment_status')) {
    //         $inspection->payment_status = $request->payment_status;
    //     }

    //     $inspection->save();

    //     return $this->apiResponse('Inspection request updated.', $inspection);
    // }

    /**
     * Delete an inspection request (if allowed).
     */
    public function destroy($id)
    {
        $inspection = InspectionRequest::findOrFail($id);

        if ($inspection->status !== 'pending') {
            return $this->apiError('Only pending requests can be deleted.', [], 403);
        }

        $inspection->delete();

        return $this->apiResponse('Inspection request deleted.');
    }

    /**
     * List all inspectors (users with inspector role).
     */
    public function getInspectors(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $query = User::role('inspector');

        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $inspectors = $query->paginate($perPage);

        return $this->apiPaginatedResponse('Inspectors list fetched.', $inspectors);
    }


// public function getInspectorAssignedRequests(Request $request)
// {
//     $user = Auth::user();

//     if (!$user->hasRole('inspector')) {
//         return $this->apiError('You are not authorized to access this resource.', [], 403);
//     }

//     $status = $request->query('status');

//     $requests = InspectionRequest::with([
//             'user',
//             'vehicleAd' => function ($query) {
//                 $query->with((new VehicleAd)->getAllRelations());
//             },
//             'city:id,name',
//             'state:id,name'
//         ])
//         ->where('type', 'vendor')
//         ->where('inspector_id', $user->id)
//         ->when($status, function ($query) use ($status) {
//             $query->where('status', $status);
//         })
//         ->orderByDesc('created_at')
//         ->paginate(10);

//     return $this->apiPaginatedResponse(
//         'Assigned inspection requests fetched successfully.',
//         $requests
//     );
// }



// to return report id
public function getInspectorAssignedRequests(Request $request)
{
    $user = Auth::user();

    if (!$user->hasRole('inspector')) {
        return $this->apiError('You are not authorized to access this resource.', [], 403);
    }

    $status = $request->query('status');

    $requests = InspectionRequest::with([
            'user',
            'vehicleAd' => function ($query) {
                $query->with((new VehicleAd)->getAllRelations());
            },
            'city:id,name',
            'state:id,name'
        ])
        ->where('type', 'vendor')
        ->where('inspector_id', $user->id)
        ->when($status, function ($query) use ($status) {
            $query->where('status', $status);
        })
        ->orderByDesc('created_at')
        ->paginate(10);

    // add completed_report_id if status = completed
    $requests->getCollection()->transform(function ($request) {
        $request->completed_report_id = null;

        if ($request->status === 'completed') {
            $report = InspectionReport::where('inspection_request_id', $request->id)
                ->first();

            $request->completed_report_id = $report ? $report->id : null;
        }

        return $request;
    });

    return $this->apiPaginatedResponse(
        'Assigned inspection requests fetched successfully.',
        $requests
    );
}

}
