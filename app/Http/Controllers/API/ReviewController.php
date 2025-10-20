<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Models\InspectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use App\Helpers\NotificationHelper;


class ReviewController extends Controller
{
    use ApiResponseTrait;

    /**
     * Create Review
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'inspection_request_id' => 'required|exists:inspection_requests,id',
                'reviewed_id' => 'nullable|exists:users,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $reviewerId = Auth::id();
            $inspection = InspectionRequest::with('vehicleAd')->find($request->inspection_request_id);
            $user = Auth::user();


            if (!$inspection) {
                return $this->apiError('Inspection request not found.', [], 404);
            }

            // Determine who is being reviewed
            if (strtolower($inspection->type) === 'self') {
                $reviewedId = $inspection->vehicleAd?->user_id;

                if (!$reviewedId) {
                    return $this->apiError('Vehicle ad owner not found for self inspection.', [], 422);
                }
            } else {
                $reviewedId = $request->reviewed_id;

                if (
                    !(
                        ($inspection->user_id == $reviewerId && $inspection->inspector_id == $reviewedId) ||
                        ($inspection->user_id == $reviewedId && $inspection->inspector_id == $reviewerId)
                    )
                ) {
                    return $this->apiError('Invalid relationship between reviewer and reviewed for this inspection.', [], 422);
                }
            }

            // Prevent self-review
            if ($reviewerId == $reviewedId) {
                return $this->apiError('You cannot review yourself.', [], 403);
            }

            // Only allow review after inspection is completed, rejected, or self
            if (!in_array(strtolower($inspection->status), ['completed', 'rejected', 'self'])) {
                return $this->apiError('Review allowed only after inspection is completed, rejected, or self.', [], 403);
            }

            // Prevent duplicate review
            $exists = Review::where('inspection_request_id', $inspection->id)
                ->where('reviewer_id', $reviewerId)
                ->where('reviewed_id', $reviewedId)
                ->exists();

            if ($exists) {
                return $this->apiError('You have already reviewed this inspection.', [], 409);
            }

            // Create the review
            $review = Review::create([
                'inspection_request_id' => $inspection->id,
                'reviewer_id' => $reviewerId,
                'reviewed_id' => $reviewedId,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
              NotificationHelper::sendTemplateNotification(
                    $reviewedId,
                    'reviewRating',
                    ['username' => $user->name],
                    ['inspection_request_id'=>$inspection->id,'rating' => $request->rating,'comment'=>$request->comment,'reviewer_id' => $reviewerId,'reviewed_id' => $reviewedId,'user_id'=>$user->id,'name'=>$user->name,'role'=>$user->role,'profile_image'=>$user->profile_image]
                );

            return $this->apiResponse('Review added successfully.', $review->load('reviewer', 'reviewed'), 201);
        } catch (\Exception $e) {
            return $this->apiError('Something went wrong while adding review.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get All Reviews Received by a User
     */
    public function userReviews($id)
    {
        try {
            $user = User::with('reviewsReceived.reviewer')->find($id);

            if (!$user) {
                return $this->apiError('User not found.', [], 404);
            }

            return $this->apiResponse('User reviews fetched successfully.', $user->reviewsReceived);
        } catch (\Exception $e) {
            return $this->apiError('Failed to fetch user reviews.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update Review
     */
    public function update(Request $request, $id)
    {
        try {
            $review = Review::where('id', $id)
                ->where('reviewer_id', Auth::id())
                ->first();

            if (!$review) {
                return $this->apiError('Review not found or unauthorized.', [], 404);
            }

            $request->validate([
                'rating' => 'sometimes|required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $review->update($request->only('rating', 'comment'));

            return $this->apiResponse('Review updated successfully.', $review);
        } catch (\Exception $e) {
            return $this->apiError('Failed to update review.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete Review
     */
    public function destroy($id)
    {
        try {
            $review = Review::where('id', $id)
                ->where('reviewer_id', Auth::id())
                ->first();

            if (!$review) {
                return $this->apiError('Review not found or unauthorized.', [], 404);
            }

            $review->delete();

            return $this->apiResponse('Review deleted successfully.');
        } catch (\Exception $e) {
            return $this->apiError('Failed to delete review.', ['error' => $e->getMessage()], 500);
        }
    }
}
