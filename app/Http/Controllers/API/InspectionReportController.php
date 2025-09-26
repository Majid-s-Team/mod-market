<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InspectionRequest;
use App\Models\InspectionReport;
use App\Traits\ApiResponseTrait;

class InspectionReportController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all reports for the logged-in inspector
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->hasRole('inspector')) {
            return $this->apiError('Unauthorized', [], 403);
        }

        $reports = InspectionReport::with([
            'inspectionRequest.user:id,name,email,contact_number,profile_image',
            'inspectionRequest.vehicleAd' => function ($q) {
                $q->with($q->getModel()->getAllRelations());
            }
        ])
            ->whereHas('inspectionRequest', function ($q) use ($user) {
                $q->where('inspector_id', $user->id);
            })
            ->latest()
            ->get();

    //  rating added and 100 score
 $reportsWithRating = $reports->map(function ($report) {
    $reportArray = $report->toArray();

    // average_score ko out of 100 me convert karo
    $scoreOutOf100 = ($report->average_score / 5) * 100;

    // Response fields set karo
        $reportArray['rating'] = round(num: $report->average_score); // integer rating

    $reportArray['average_score'] = round($scoreOutOf100, 2); // out of 100

    return $reportArray;
});
    return $this->apiResponse('Inspection reports retrieved', $reportsWithRating);
        // return $this->apiResponse('Inspection reports retrieved', $reports);
    }

    /**
     * Show a single report
     */
    public function show($id)
    {
        $user = Auth::user();

        $report = InspectionReport::with([
            'inspectionRequest.user:id,name,email,contact_number,profile_image',
            'inspectionRequest.vehicleAd' => function ($q) {
                $q->with($q->getModel()->getAllRelations());
            }
        ])
            ->whereHas('inspectionRequest', function ($q) use ($user) {
                $q->where('inspector_id', $user->id);
            })
            ->find($id);

        if (!$report) {
            return $this->apiError('Report not found or unauthorized', [], 404);
        }

 // avreage score by 100.
    $reportWithPercentage = $report->toArray();
    $reportWithPercentage['average_score'] = ($report->average_score / 5) * 100;
    $reportWithPercentage['rating'] = round(num: $report->average_score);



        return $this->apiResponse('Inspection report retrieved', $reportWithPercentage);
    }

    /**
     * Create a new inspection report
     */
    public function store(Request $request, $inspectionRequestId)
    {
        $user = Auth::user();

        if (!$user->hasRole('inspector')) {
            return $this->apiError('Unauthorized', [], 403);
        }
        // dd($inspectionRequestId);

        $inspectionRequest = InspectionRequest::where('id', $inspectionRequestId)
            ->where('inspector_id', $user->id)
            ->first();

        if (!$inspectionRequest) {
            return $this->apiError('Inspection request not found', [], 404);
        }

        if (InspectionReport::where('inspection_request_id', $inspectionRequest->id)->exists()) {
            return $this->apiError('Report already exists for this request', [], 400);
        }

        $validated = $this->validateReport($request);

        $averageScore = $this->calculateAverageScore($validated);

        $report = InspectionReport::create(array_merge($validated, [
            'inspection_request_id' => $inspectionRequest->id,
            'average_score' => $averageScore
        ]));

        $inspectionRequest->update(['status' => 'completed']);

    // avreage score by 100.
    $reportWithPercentage = $report->toArray();
    $reportWithPercentage['average_score'] = ($report->average_score / 5) * 100;
    $reportWithPercentage['rating'] =round(num: $report->average_score);


    return $this->apiResponse('Inspection report created', $reportWithPercentage);

        // return $this->apiResponse('Inspection report created', $report);
    }

    /**
     * Update a report
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $report = InspectionReport::whereHas('inspectionRequest', function ($q) use ($user) {
            $q->where('inspector_id', $user->id);
        })->find($id);

        if (!$report) {
            return $this->apiError('Report not found or unauthorized', [], 404);
        }

        $validated = $this->validateReportUpdate($request);

        if ($this->hasTestFields($validated)) {
            $averageScore = $this->calculateAverageScore($validated + $report->toArray());
            $validated['average_score'] = $averageScore;
        }

        $report->update($validated);

        return $this->apiResponse('Inspection report updated', $report);
    }

    private function hasTestFields($data)
    {
        $testFields = [
            'engine_test',
            'transmission_test',
            'braking_system_test',
            'suspension_system_test',
            'interior_exterior_test',
            'tyre_vehicle_test',
            'computer_electronics_test'
        ];
        return collect($testFields)->intersect(array_keys($data))->isNotEmpty();
    }


    /**
     * Delete a report
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $report = InspectionReport::whereHas('inspectionRequest', function ($q) use ($user) {
            $q->where('inspector_id', $user->id);
        })->find($id);

        if (!$report) {
            return $this->apiError('Report not found or unauthorized', [], 404);
        }

        $report->delete();

        return $this->apiResponse('Inspection report deleted', []);
    }
    private function validateReportUpdate(Request $request)
    {
        return $request->validate([
            'engine_test' => 'sometimes|in:poor,average,good,excellent,perfect',
            'engine_description' => 'nullable|string',
            'transmission_test' => 'sometimes|in:poor,average,good,excellent,perfect',
            'transmission_description' => 'nullable|string',
            'braking_system_test' => 'sometimes|in:poor,average,good,excellent,perfect',
            'braking_system_description' => 'nullable|string',
            'suspension_system_test' => 'sometimes|in:poor,average,good,excellent,perfect',
            'suspension_system_description' => 'nullable|string',
            'interior_exterior_test' => 'sometimes|in:poor,average,good,excellent,perfect',
            'interior_exterior_description' => 'nullable|string',
            'tyre_vehicle_test' => 'sometimes|in:poor,average,good,excellent,perfect',
            'tyre_vehicle_description' => 'nullable|string',
            'computer_electronics_test' => 'sometimes|in:poor,average,good,excellent,perfect',
            'computer_electronics_description' => 'nullable|string',
            'final_remarks' => 'nullable|string'
        ]);
    }


    /**
     * Validation rules
     */
    private function validateReport(Request $request)
    {
        return $request->validate([
            'engine_test' => 'required|in:poor,average,good,excellent,perfect',
            'engine_description' => 'nullable|string',
            'transmission_test' => 'required|in:poor,average,good,excellent,perfect',
            'transmission_description' => 'nullable|string',
            'braking_system_test' => 'required|in:poor,average,good,excellent,perfect',
            'braking_system_description' => 'nullable|string',
            'suspension_system_test' => 'required|in:poor,average,good,excellent,perfect',
            'suspension_system_description' => 'nullable|string',
            'interior_exterior_test' => 'required|in:poor,average,good,excellent,perfect',
            'interior_exterior_description' => 'nullable|string',
            'tyre_vehicle_test' => 'required|in:poor,average,good,excellent,perfect',
            'tyre_vehicle_description' => 'nullable|string',
            'computer_electronics_test' => 'required|in:poor,average,good,excellent,perfect',
            'computer_electronics_description' => 'nullable|string',
            'final_remarks' => 'nullable|string'
        ]);
    }


    /**Only validate Final Remarks on update api */

    private function validateFinalRemarks(Request $request)
    {
        return $request->validate([
            'final_remarks' => 'nullable|string'
        ]);
    }

    /**
     * Calculate average score from test results
     */
        private function calculateAverageScore($data)
    {
        $scoreMap = [
            'poor' => 1,
            'average' => 2,
            'good' => 3,
            'excellent' => 4,
            'perfect' => 5
        ];

        $fields = [
            'engine_test',
            'transmission_test',
            'braking_system_test',
            'suspension_system_test',
            'interior_exterior_test',
            'tyre_vehicle_test',
            'computer_electronics_test'
        ];

        $scores = [];
        foreach ($fields as $field) {
            if (!empty($data[$field])) {
                $scores[] = $scoreMap[$data[$field]];
            }
        }

        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : null;
    }

    /**
     * View logged-in user's own inspection report
     */
    public function myReport($id = null)
    {
        $user = Auth::user();
        $query = InspectionReport::with([
            'inspectionRequest.user:id,name,email,contact_number,profile_image',
            'inspectionRequest.vehicleAd' => function ($q) {
                $q->with($q->getModel()->getAllRelations());
            }
        ])
            ->whereHas('inspectionRequest', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        // dd($query);

        // If ID is provided, get single record
        if ($id) {
            $report = $query->find($id);

            if (!$report) {
                return $this->apiError('Report not found or unauthorized', [], 404);
            }

            return $this->apiResponse('Your inspection report retrieved successfully', $report);
        }

        // Otherwise, return all reports for this user
        $reports = $query->latest()->get();
        return $this->apiResponse('All your inspection reports retrieved successfully', $reports);
    }

    public function earningsOrInvestments(Request $request)
    {
        try {
            $user = Auth::user();

            // Common filters
            $startDate = $request->input('start_date');
            $endDate   = $request->input('end_date');
            $year      = $request->input('year');
            $month     = $request->input('month');

            // Build base query depending on role
            if ($user->hasRole('inspector')) {
                $query = InspectionRequest::where('inspector_id', $user->id)
                    ->where('payment_status', 'paid');
                $amountColumn = 'inspector_price';
                $title = 'Inspector Earnings';
            } elseif ($user->hasRole('user')) {
                $query = InspectionRequest::where('user_id', $user->id)
                    ->where('payment_status', 'paid');
                $amountColumn = 'inspector_price'; // or "total_price" if investments mean full payment
                $title = 'My Investments';
            } else {
                return $this->apiError('Unauthorized role', [], 403);
            }

            // Apply filters
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
            if ($year) {
                $query->whereYear('created_at', $year);
            }
            if ($month) {
                $query->whereMonth('created_at', $month);
            }

            // Get transactions
            $transactions = $query->with([
                'user:id,name,email',
                'inspector:id,name,email',
                'vehicleAd'
            ])->latest()->get();

            if ($transactions->isEmpty()) {
                return $this->apiError('No transactions found', [], 200);
            }

            $totalAmount = $transactions->sum($amountColumn);

            return $this->apiResponse($title, [
                'total_amount'        => $totalAmount,
                'currency'            => 'USD',
                'total_transactions'  => $transactions->count(),
                'transactions'        => $transactions
            ]);
        } catch (\Exception $e) {
            return $this->apiError('Something went wrong', [
                'exception' => $e->getMessage()
            ], 500);
        }
    }




}
