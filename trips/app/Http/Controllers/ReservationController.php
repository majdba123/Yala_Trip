<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Trip;

class ReservationController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function panding_reservation()
    {
        // Get the authenticated user
        $user = auth()->user();
        // Retrieve all reservations with a status of 'pending' for the user
        $reservations = Reservation::where('user_id', $user->id)
                                   ->where('status', 'panding')
                                   ->get();
        // Return the reservations as a JSON response
        return response()->json($reservations);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        //
    }

    public function booking(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|integer',
            'num_passenger' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $trip= Trip::findOrfail($id);

        $trip_price= $trip->Path->price;


        $reservation = Reservation::where('trip_id', $id)
        ->where('status', 'panding')
        ->sum('num_passenger');


        if ($reservation + $request->num_passenger > $trip->num_passenger) {
            return response()->json([
                'message' => 'There are not enough seats available at this time',
            ]);
        }

        $total_price = $trip_price * $request->num_passenger;
        if ($request->price != $total_price) {
            return response()->json([
                'message' => 'The price is not valid',
            ]);
        }

        $user = auth()->user();
        if ($user->point < $total_price) {
            // User does not have enough points
            return response()->json([
                'message' => 'You do not have enough points to make this reservation',
            ]);
        }

        $booking = new Reservation();
        $booking->trip_id = $id;
        $booking->user_id = auth()->id();
        $booking->price = $total_price;
        $booking->num_passenger = $request->num_passenger;
        $booking->save();


        $reservation1 = Reservation::where('trip_id', $id)
        ->where('status', 'panding')
        ->sum('num_passenger');


        if ($reservation1 == $trip->num_passenger) {
            $trip->status = 'complete';
            $trip->save();
        }

        $user->point -= $total_price;
        $user->save();


        // Update driver points
        $driver = $trip->Driver->user;
        $driver->point += $total_price;
        $driver->save();

        return response()->json($booking);
    }
}
