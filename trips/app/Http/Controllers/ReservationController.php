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
    public function panding_reservation(Request $request)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Get the status parameter from the request
        $status = $request->input('status');
        // Define a list of valid status values
        $validStatusValues = ['complete', 'out', 'finished', 'pending'];
        // Check that the status parameter is valid
        if (!in_array($status, $validStatusValues)) {
            // Return an error response if the status parameter is invalid
            return response()->json(['error' => 'Invalid status parameter'], 400);
        }

        // Retrieve all reservations with the specified status for the user
        $reservations = Reservation::where('user_id', $user->id)
                                   ->where('status', $status)
                                   ->get();

        $transformedReservations = $reservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'trip_id' => $reservation->trip_id,
                'break' => $reservation->break->name,
                'user_name' => $reservation->user->name,
                'from' => $reservation->Trip->Path->from,
                'to' => $reservation->Trip->Path->to,
                'price' => $reservation->price,
                'num_passenger' => $reservation->num_passenger,
                'status' => $reservation->status,
                // add other reservation fields as needed
            ];
        });

        return response()->json($transformedReservations);
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
            'break_id' => 'nullable|integer|exists:breakings,id'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $trip= Trip::findOrfail($id);

        $trip_price= $trip->Path->price;


        $reservation = Reservation::where('trip_id', $id)
        ->where('status', 'pending')
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
        if ($request->has('break_id')) {
            $booking->breaking_id = $request->input('break_id');
        }
        $booking->price = $total_price;
        $booking->num_passenger = $request->num_passenger;
        $booking->save();


        $reservation1 = Reservation::where('trip_id', $id)
        ->where('status', 'pending')
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

        $data = [
            'reserrvation_id' => $booking->id,
            'trip_id' => $booking->trip_id,
            'user' => $booking->user->name,
            'num_passenger' =>  $booking->num_passenger,
            'price' =>  $booking->price,
            'status' =>  $booking->status ?: 'pending',
        ];
        if ($booking->breaking_id) {
            $data['breaking_id'] = $booking->break->name;
        }
        $bookingData[] = $data;
        return response()->json($bookingData);
    }
}
