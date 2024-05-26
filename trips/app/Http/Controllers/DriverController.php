<?php

namespace App\Http\Controllers;
use App\Models\Trip;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Reservation;




class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
        ], [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'email.unique' => 'Email has already been taken',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters long',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 422);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $id= $user->id;
        $driver = Driver::create([
            'user_id' => $id,
        ]);


        return response()->json([
            'message' => 'Driver Created ',
        ]);
    }


    /**
     * Display the specified resource.
     */


    public function check_QR_COM(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|integer',
            'trip_id' => 'required|integer|exists:trips,id',
            'id' => 'required|integer|exists:reservations,id',
            'user_id' => 'required|integer|exists:users,id',
            'num_passenger' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }


        // Get the reservation ID from the request
        $id = $request->input('id');

        // Retrieve the reservation by its ID
        $reservation = Reservation::findOrFail($id);

        // Check if the reservation exists and the user making the request is the owner
        if ($reservation) {
            // Update the reservation status to 'complete'
            $reservation->status = 'complete';
            $reservation->save();

            // Return a JSON response indicating success
            return response()->json(['message' => 'Reservation status updated to complete']);
        } else {
            // Return a JSON response indicating failure
            return response()->json(['message' => 'Reservation not found '], 404);
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function out_reservation($id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation) {

            $reservation->status = 'out';
            $reservation->save();

            $trip_id= $reservation->$trip_id;

            $trip= Trip::findOrfail($trip_id);

            $reservationn_fineshed_out = Reservation::where('trip_id', $trip_id)
            ->where('status', '!complete and !pending')
            ->sum('num_passenger');

            $reservationn_complete_pending = Reservation::where('trip_id', $trip_id)
            ->where('status', '!finished and !out')
            ->sum('num_passenger');

            if ($reservationn  > $trip->num_passenger) {
                return response()->json([
                    'message' => 'There are not enough seats available at this time',
                ]);
            }

            return response()->json(['message' => 'Reservation status updated to out']);
        } else {
            // Return a JSON response indicating failure
            return response()->json(['message' => 'Reservation not found '], 404);
        }


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver $driver)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver)
    {
        //
    }
}
