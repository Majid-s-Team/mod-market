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
            'card_holder'   => 'required|string|max:255',
            'card_number'   => 'required|string|min:13|max:19|unique:cards,card_number',
            'expiry_month'  => 'required|digits:2',
            'expiry_year'   => 'required|digits:4',
            'cvv'           => 'required|digits:3',
        ]);

        if ($validator->fails()) {
            return $this->apiError('Validation error', $validator->errors()->toArray(), 422);
        }

        $card = Card::create([
            'user_id'       => Auth::id(),
            'card_holder'   => $request->card_holder,
            'card_number'   => $request->card_number,
            'expiry_month'  => $request->expiry_month,
            'expiry_year'   => $request->expiry_year,
            'cvv'           => $request->cvv,
        ]);

        return $this->apiResponse('Card added successfully', $card, 201);
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
            return $this->apiError('Card not found', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'card_holder'   => 'sometimes|required|string|max:255',
            'card_number'   => 'sometimes|required|string|min:13|max:19|unique:cards,card_number,' . $id,
            'expiry_month'  => 'sometimes|required|digits:2',
            'expiry_year'   => 'sometimes|required|digits:4',
            'cvv'           => 'sometimes|required|digits:3',
        ]);

        if ($validator->fails()) {
            return $this->apiError('Validation error', $validator->errors()->toArray(), 422);
        }

        $card->update($request->only(['card_holder', 'card_number', 'expiry_month', 'expiry_year', 'cvv']));

        return $this->apiResponse('Card updated successfully', $card);
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
