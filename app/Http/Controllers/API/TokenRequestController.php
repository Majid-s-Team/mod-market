<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TokenRequest;
use App\Models\VehicleAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class TokenRequestController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_ad_id' => 'required|exists:vehicle_ads,id',
            'token_money' => 'required|numeric|min:1',
            'concern' => 'nullable|string|max:1000'
        ]);

        $vehicleAd = VehicleAd::findOrFail($request->vehicle_ad_id);

        if ($vehicleAd->user_id == Auth::id()) {
            return $this->apiError('You cannot send a token request to your own ad.', [], 403);
        }

        if ($request->token_money > $vehicleAd->price) {
            return $this->apiError('Token money cannot exceed the vehicle price.', [], 422);
        }

        // Check if same buyer has already sent request for this ad
        $alreadyRequested = TokenRequest::where('vehicle_ad_id', $vehicleAd->id)
            ->where('buyer_id', Auth::id())
            ->exists();

        if ($alreadyRequested) {
            return $this->apiError('You have already sent a token request for this ad.', [], 422);
        }
        // agar is vehcile ad ka against ma token approved ho chuka ha to
        //  semd error bhaj de ka vehcile ad owner has already accpet somesone token

        $alreadyAccepted = TokenRequest::where('vehicle_ad_id', $request->vehicle_ad_id)
            ->where('status', 'approved')
            ->exists();

        if ($alreadyAccepted) {
            return $this->apiError("The Owner of this Vehicle Ad has already accepted someone's token", [], 422);
        }

        $tokenRequest = TokenRequest::create([
            'vehicle_ad_id' => $vehicleAd->id,
            'buyer_id' => Auth::id(),
            'seller_id' => $vehicleAd->user_id,
            'token_money' => $request->token_money,
            'concern' => $request->concern,
            'status' => 'pending'
        ]);

        return $this->apiResponse('Token request sent successfully.', $tokenRequest, 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $tokenRequest = TokenRequest::findOrFail($id);

        if ($tokenRequest->seller_id != Auth::id()) {
            return $this->apiError('You are not authorized to update this request.', [], 403);
        }

        if ($tokenRequest->status !== 'pending') {
            return $this->apiError('Status cannot be changed once it has been approved or rejected.', [], 422);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reject_reason' => 'required_if:status,rejected|nullable|string|max:1000',
        ]);

        $update = [
            'status' => $request->status,
            'reject_reason' => $request->status === 'rejected' ? ($request->reject_reason ?? null) : null,
        ];

        $tokenRequest->update($update);

        $msg = $request->status === 'approved'
            ? 'Request approved successfully.'
            : 'Request rejected successfully.';

        return $this->apiResponse($msg, $tokenRequest);
    }

    public function getAllPublishedRequests()
    {
        $tokenRequests = TokenRequest::with([
            'vehicleAd' => function ($query) {
                $query->with((new VehicleAd)->getAllRelations());
            },
            'buyer',
            'seller'
        ])
            ->where('buyer_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->apiResponse('All published token requests fetched successfully.', $tokenRequests);
    }

    public function getMyTokenRequests(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $tokenRequests = TokenRequest::with([
                'vehicleAd' => function ($query) {
                    $query->with((new VehicleAd)->getAllRelations());
                },
                'buyer'
            ])
            ->where('seller_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->apiPaginatedResponse(
            'All token requests sent to you fetched successfully.',
            $tokenRequests
        );
    }


}
