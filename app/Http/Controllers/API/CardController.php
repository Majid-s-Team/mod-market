<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\User;
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

    public function createStripeCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return $this->apiError('Validation error', $validator->errors()->toArray(), 422);
        }

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $customer_id = Auth::user()->gateway_customer_id;
        try {
            $card = $stripe->customers->createSource(
                $customer_id,
                ['source' => $request->token]
            );
        } catch (\Exception $e) {
            return $this->apiError('Failed to create card in Stripe: ' . $e->getMessage(), [], 500);
        }
        $card = Card::create([
            'user_id' => Auth::id(),
            'card_id' => $card->id,
            'type' => 'card',
            'card_holder' => $card->name ?? null,
            'card_number' => '**** **** **** ' . substr($card->last4, -4),
            'expiry_month' => $card->exp_month ?? null,
            'expiry_year' => $card->exp_year ?? null,
            'brand' => $card->brand ?? null,
        ]);
        $cards_count = Card::where('user_id', Auth::id())->whereNotNull('card_id')->count();
        if ($cards_count === 1) {
            $this->setDefaultCard($card->id);
        }
        return $this->apiResponse('Stripe card created successfully');
    }

    public function deleteStripeCard($id)
    {
        $card = Card::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$card) {
            return $this->apiError('Card not found', [], 404);
        }
        if ($card->is_default) {
            return $this->apiError('Cannot delete default card', [], 400);
        }
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $customer_id = Auth::user()->gateway_customer_id;
        try {
            $stripe->customers->deleteSource(
                $customer_id,
                $card->card_id
            );
        } catch (\Exception $e) {
            return $this->apiError('Failed to delete card from Stripe: ' . $e->getMessage(), [], 500);
        }

        $card->delete();

        return $this->apiResponse('Stripe card deleted successfully');
    }

    public function listStripeCards()
    {
        $stripecards = Card::where('user_id', Auth::id())->whereNotNull('card_id')->get();
        if ($stripecards->isEmpty()) {
            return $this->apiResponse('No Stripe cards found', [], 404);
        }
        return $this->apiResponse('Stripe cards fetched successfully', $stripecards);
    }


    public function setDefaultCard($id)
    {
        $card = Card::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$card) {
            return $this->apiError('Card not found', [], 404);
        }

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $customer_id = Auth::user()->gateway_customer_id;
        try {
            $stripe->customers->update(
                $customer_id,
                ['default_source' => $card->card_id]
            );
            $card->is_default = true;
            $card->save();
        } catch (\Exception $e) {
            return $this->apiError('Failed to set default card in Stripe: ' . $e->getMessage(), [], 500);
        }

        Card::where('user_id', Auth::id())->where('id', '!=', $id)->update(['is_default' => false]);
        return $this->apiResponse('Default card set successfully', $card);
    }

    public function generateConnectAccountLink()
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $connect_account_id = Auth::user()->gateway_connect_id;

        if (!$connect_account_id) {
            return $this->apiError('Connect account not found', [], 404);
        }

        try {
            $accountLink = $stripe->accountLinks->create([
                'account' => $connect_account_id,
                'refresh_url' => url('/stripe/connect/refresh'),
                'return_url' => url('/stripe/connect/return'),
                'type' => 'account_onboarding',
            ]);
        } catch (\Exception $e) {
            return $this->apiError('Failed to generate connect account link: ' . $e->getMessage(), [], 500);
        }

        return $this->apiResponse('Connect account link generated successfully', ['url' => $accountLink->url]);
    }

    public function checkConnectAccountStatus()
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $connect_account_id = Auth::user()->gateway_connect_id;

        if (!$connect_account_id) {
            return $this->apiError('Connect account not found', [], 404);
        }

        try {
            $account = $stripe->accounts->retrieve($connect_account_id);
        } catch (\Exception $e) {
            return $this->apiError('Failed to retrieve connect account status: ' . $e->getMessage(), [], 500);
        }

        User::where('id', Auth::id())->update([
            'gateway_charges_enabled' => $account->charges_enabled,
            'gateway_payouts_enabled' => $account->payouts_enabled,
        ]);
        return $this->apiResponse('Connect account status retrieved successfully', User::find(Auth::id()));
    }
}
