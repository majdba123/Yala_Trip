<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
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
    public function createRating(Request $request, $trip_id)
    {
        // Get the authenticated user
        $user = auth()->user();
        // Get the rating value from the request (1-5)
        $rating = $request->input('num');
        // Validate the rating value
        if (!in_array($rating, range(1, 5))) {
            return response()->json(['error' => 'Invalid rating value'], 400);
        }
        // Get the trip ID from the request
        // Create a new rating instance
        $ratingInstance = new Rating();
        // Set the rating value, user ID, and trip ID
        $ratingInstance->num = $rating;
        $ratingInstance->user_id = $user->id;
        $ratingInstance->trip_id = $trip_id;
        // Save the rating instance
        $ratingInstance->save();
        // Return a success response
        return response()->json(['message' => 'Rating submited successfully']);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Rating $rating)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rating $rating)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rating $rating)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rating $rating)
    {
        //
    }
}
