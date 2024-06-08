<?php

namespace App\Http\Controllers;

use App\Models\Rate_comapny;
use Illuminate\Http\Request;

class RateComapnyController extends Controller
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
    public function store(Request $request ,$company_id)
    {
        $user = auth()->user();
        // Get the rating value from the request (1-5)
        $rating = $request->input('num');
        // Validate the rating value
        if (!in_array($rating, range(1, 5))) {
            return response()->json(['error' => 'Invalid rating value'], 400);
        }
        // Get the trip ID from the request
        // Create a new rating instance
        $ratingInstance = new Rate_comapny();
        // Set the rating value, user ID, and trip ID
        $ratingInstance->num = $rating;
        $ratingInstance->user_id = $user->id;
        $ratingInstance->company_id = $company_id;
        // Save the rating instance
        $ratingInstance->save();
        // Return a success response
        return response()->json(['message' => 'Rating submited successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Rate_comapny $rate_comapny)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rate_comapny $rate_comapny)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rate_comapny $rate_comapny)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rate_comapny $rate_comapny)
    {
        //
    }
}
