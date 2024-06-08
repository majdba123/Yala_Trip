<?php

namespace App\Http\Controllers;

use App\Models\user_subscription;
use App\Models\Subscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class UserSubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id)
    {
        $subscription = Subscriptions::find($id);

        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        $type = $subscription->type;
        $price =$subscription->price;
        $user = auth()->user();
        if ($user->point < $price) {
            // User does not have enough points
            return response()->json([
                'message' => 'You do not have enough points to make this reservation',
            ]);
        }
        $user->point -= $price;
        $user->save();

        $company = $subscription->company->user;
        $company->point += $price;
        $company->save();


        $startDate = now();
        $endDate = null;

        switch ($type) {
            case 'weekly':
                $endDate = $startDate->addWeek();
                break;
            case 'monthly':
                $endDate = $startDate->addMonth();
                break;
            case 'yearly':
                $endDate = $startDate->addYear();
                break;
            default:
                return response()->json(['error' => 'Invalid subscription type'], 422);
        }

        $store = new user_subscription();
        $store->user_id = Auth::id();
        $store->subscriptions_id = $id;
        $store->end_date = $endDate;
        $store->date_start = now();
        $store->status = 'active';

        // Check if user already has a subscription with the same ID
        $existingSubscription = user_subscription::where('user_id', Auth::id())
            ->where('subscriptions_id', $id)
            ->first();

        if ($existingSubscription) {
            return response()->json(['error' => 'You already have a subscription with this ID'], 409);
        }

        $store->save();

        return response()->json(['message' => 'Store created successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(user_subscription $user_subscription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(user_subscription $user_subscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, user_subscription $user_subscription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(user_subscription $user_subscription)
    {
        //
    }
}
