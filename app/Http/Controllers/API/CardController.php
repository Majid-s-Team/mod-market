<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponseTrait;

class CardController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $cards = Card::where('user_id', Auth::id())->latest()->get();
        return $this->apiResponse('Cards fetched successfully', $cards);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:card,bank',
            'card_holder' => 'required_if:type,card|string|max:255',
            'card_number' => 'required_if:type,card|string|min:13|max:19|unique:cards,card_number',
            'expiry_month' => 'required_if:type,card|digits:2',
            'expiry_year' => 'required_if:type,card|digits:4',
            'cvv' => 'required_if:type,card|digits:3',

            'account_holder' => 'required_if:type,bank|string|max:255',
            'account_number' => 'required_if:type,bank|string|min:8|max:34|unique:cards,account_number',
            'bank_name' => 'required_if:type,bank|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->apiError('Validation error', $validator->errors()->toArray(), 422);
        }

        $card = Card::create(array_merge(
            $request->only([
                'type',
                'card_holder',
                'card_number',
                'expiry_month',
                'expiry_year',
                'cvv',
                'account_holder',
                'account_number',
                'bank_name',
                'branch_name',
                'ifsc_code'
            ]),
            ['user_id' => Auth::id()]
        ));

        return $this->apiResponse(
            $request->type === 'card' ? 'Card added successfully' : 'Bank account added successfully',
            $card,
            201
        );

    }

    public function show($id)
    {
        $card = Card::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$card) {
            return $this->apiError('Card not found', [], 404);
        }

        return $this->apiResponse('Card fetched successfully', $card);
    }

    public function update(Request $request, $id)
    {
        $card = Card::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$card) {
            return $this->apiError('Card/Bank account not found', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|in:card,bank',


            'card_holder' => 'required_if:type,card|string|max:255',
            'card_number' => 'required_if:type,card|string|min:13|max:19|unique:cards,card_number,' . $id,
            'expiry_month' => 'required_if:type,card|digits:2',
            'expiry_year' => 'required_if:type,card|digits:4',
            'cvv' => 'required_if:type,card|digits:3',


            'account_holder' => 'required_if:type,bank|string|max:255',
            'account_number' => 'required_if:type,bank|string|min:8|max:34|unique:cards,account_number,' . $id,
            'bank_name' => 'required_if:type,bank|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->apiError('Validation error', $validator->errors()->toArray(), 422);
        }

        $card->update($request->only([
            'type',
            'card_holder',
            'card_number',
            'expiry_month',
            'expiry_year',
            'cvv',
            'account_holder',
            'account_number',
            'bank_name',
            'branch_name',
            'ifsc_code'
        ]));

        return $this->apiResponse(
            ($request->type ?? $card->type) === 'card'
            ? 'Card updated successfully'
            : 'Bank account updated successfully',
            $card
        );
    }


    public function destroy($id)
    {
        $card = Card::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$card) {
            return $this->apiError('Card not found', [], 404);
        }

        $card->delete();

        return $this->apiResponse('Card deleted successfully');
    }
}
