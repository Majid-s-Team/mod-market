<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\InspectionRequest;
use App\Models\User;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use Illuminate\Validation\Rule;


class InspectionRequestController extends Controller
{
    use ApiResponseTrait;
    public function index(Request $request)
    {
        $user = Auth::user();

        $requests = InspectionRequest::with([
            'user:id,name',
            'inspector:id,name',
            'vehicleAd:id,make_id,model_id',
            'city:id,name',
            'state:id,name',
        ])
            ->when($user->hasRole('inspector'), fn($q) => $q->where('inspector_id', $user->id))
            ->when(!$user->hasRole('inspector'), fn($q) => $q->where('user_id', $user->id))
            ->orderByDesc('created_at')
            ->paginate(10);

        return $this->apiPaginatedResponse('Inspection requests fetched.', $requests);
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
    public function getInspectors()
    {
        $inspectors = User::role('inspector')
            ->select('id', 'name', 'service_rate', 'profile_image', 'city', 'state')
            ->get();

        return $this->apiResponse('Inspectors list fetched.', $inspectors);
    }

}
