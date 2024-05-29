<?php

namespace App\Http\Controllers;
use App\Models\Trip;
use App\Models\Driver;
use App\Models\breaking_Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Order_private;
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
            'id' => 'required|integer|exists:reservations,id',
            'price' => 'required|integer',
            'trip_id' => 'required|integer|exists:trips,id',
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
        // Get the reservation with the given ID
        $reservation = Reservation::find($id);

        // Check if the reservation exists and its status is 'pending'
        if (!$reservation || $reservation->status !== 'panding') {
            return response()->json(['message' => 'Invalid reservation'], 400);
        }

        // Change the reservation status to 'out'
        $reservation->status = 'out';
        $reservation->save();

        // Get the trip ID from the reservation
        $tripId = $reservation->trip_id;

        // Get the number of passengers for the trip
        $numPassengers = Trip::find($tripId)->num_passenger;

        // Get the total number of 'pending' and 'complete' reservations for the trip
        $totalReservations = Reservation::where('trip_id', $tripId)
                                         ->whereIn('status', ['panding', 'complete'])
                                         ->sum('num_passenger');

        $TRIP=Trip::find($tripId);
        // If the total number of reservations is equal to the number of passengers,
        // change the trip status to 'pending'
        if ($totalReservations < $numPassengers) {
            $TRIP->status = 'panding';
            $TRIP->save();
        }

        // Return the updated reservation
        return response()->json(['message' => 'Reservations updated successfully']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function current_reservation($id)
    {
        $reservations = Reservation::where('trip_id', $id)
                                    ->where('status', '!=', 'out')
                                    ->where('status', '!=', 'finished')
                                   ->get();

        $transformedReservations = $reservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'user_name' => $reservation->user->name,
                'from' => $reservation->Trip->Path->from,
                'to' => $reservation->Trip->Path->to,
                'price' => $reservation->price,
                'num_passenger' => $reservation->num_passenger,
                'status' => $reservation->status,
                'breaks' => $reservation->break->name,
                // add other reservation fields as needed
            ];
        });

        return response()->json($transformedReservations);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function current_trip()
    {
        $trips = Trip::whereIn('status', ['pandding', 'complete'])
                    ->first();
        $breaks = $trips->breaking_Trip; // assuming you have a breaksTrips relationship in your Trip model
        $data = [
            'trip_id' => $trips->id,
            'from' => $trips->Path->from,
            'to' => $trips->Path->to,
            'driver' =>  $trips->Driver->user->name,
            'num_passenger' =>  $trips->num_passenger,
            'status' =>  $trips->status,
            'breaks' => $breaks->sortBy('break.sorted')->map(function($breakk) {
                return [
                    'id' => $breakk->id,
                    'sorted' => $breakk->break->sorted,
                    'name' => $breakk->break->name,
                    // add other break fields as needed
                ];
            })->all(),
        ];
        $tripData[] = $data;
        return response()->json($tripData);
    }

    public function check_QR_finished(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:reservations,id',
            'price' => 'required|integer',
            'trip_id' => 'required|integer|exists:trips,id',
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
            $reservation->status = 'finished';
            $reservation->save();
            $tripId = $reservation->trip_id;
            // Get the number of passengers for the trip
            $numPassengers = Trip::find($tripId)->num_passenger;
            // Get the total number of 'pending' and 'complete' reservations for the trip
            $totalReservations = Reservation::where('trip_id', $tripId)
                                             ->whereIn('status', ['panding', 'complete'])
                                             ->sum('num_passenger');

            $TRIP=Trip::find($tripId);
            // If the total number of reservations is equal to the number of passengers,
            // change the trip status to 'pending'
            if ($totalReservations < $numPassengers) {
                $TRIP->status = 'panding';
                $TRIP->save();
            }
            // Return the updated reservation
            return response()->json(['message' => 'Reservations updated successfully']);
    }
    }
    public function finish_trip($id)
    {
        $user = Auth::user();
        // Get the trip with the given ID
        $trip = Trip::findOrFail($id);

        if ($user->Driver->id !== $trip->driver_id ) {

            // Return an error message if the user is not authorized
            return response()->json(['message' => 'you are not owner of this trip'], 401);

        }
        // Change the trip status to 'finished'
        $trip->status = 'finished';
        $trip->save();

        // Get all reservations for this trip with a status of 'completed' or 'pending'
        $reservations = Reservation::where('trip_id', $id)
                                   ->whereIn('status', ['complete', 'panding'])
                                   ->get();

        // Set the status of each reservation to 'finished' or 'out'
        foreach ($reservations as $reservation) {
            if ($reservation->status === 'complete') {
                $reservation->status = 'finished';
            } elseif ($reservation->status === 'panding') {
                $reservation->status = 'out';
            }
            $reservation->save();
        }

        // Return a success message
        return response()->json(['message' => 'Trip finished successfully']);
    }


    public function break_finished($id ,Request $request)
    {
        $user = Auth::user();
        $trip = Trip::findOrFail($id);
        if ($user->Driver->id !== $trip->driver_id ) {

            // Return an error message if the user is not authorized
            return response()->json(['message' => 'you are not owner of this trip'], 401);

        }
        // Get the trip and its breaks for the given break_id
        $validator = Validator::make($request->all(), [
            'break_id' => 'required|integer|exists:breakings,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
    // Get the trip and its breaks for the given break_id
        $breakingTrip = breaking_Trip::where('trip_id', $id)
                                    ->where('breaking_id', $request->input('break_id'))
                                    ->first();
        if ($breakingTrip) {
            // Change the status of the breaks to 'complete'
            $breakingTrip->status = 'complete';
            $breakingTrip->save();
            // Return a success message
            return response()->json(['message' => 'Break finished successfully']);
        } else {
            // Return an error message if the break_id is not found
            return response()->json(['error' => 'Break not found'], 404);
        }

    }

    public function resr_of_breaks($id, Request $request)
    {
        $user = Auth::user();
        $trip = Trip::findOrFail($id);
        if ($user->Driver->id !== $trip->driver_id ) {

            // Return an error message if the user is not authorized
            return response()->json(['message' => 'you are not owner of this trip'], 401);

        }
        // Validate the request
        $validator = Validator::make($request->all(), [
            'break_id' => 'required|integer|exists:breakings,id',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        // Get the reservations for the given trip_id and break_id
        $reservations = Reservation::where('trip_id', $id)
                                   ->where('breaking_id', $request->input('break_id'))
                                   ->where('status', 'panding')
                                   ->get();

        // Initialize an empty array to store the custom JSON response
        $response = [];

        // Loop through each reservation and add it to the custom JSON response
        foreach ($reservations as $reservation) {
            $response[] = [
                'id' => $reservation->id,
                'user_name' => $reservation->user->name,
                'from' => $reservation->Trip->Path->from,
                'to' => $reservation->Trip->Path->to,
                'price' => $reservation->price,
                'num_passenger' => $reservation->num_passenger,
                'status' => $reservation->status,
                'breaks' => $reservation->break->name,
            ];
        }
        // Return the custom JSON response
        return response()->json($response);
    }

    public function info()
    {
        $user = Auth::user();
        $password=$user->password;
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'point' =>  $user->point,
            'phone' =>  $user->phone,
            'lat' =>  $user->lat,
            'lang' =>  $user->lang,
            'model_car' =>  $user->Driver->model_car,
            'number_car' =>  $user->Driver->number_car,
            'color_car' =>  $user->Driver->color_car,
        ];
        $profile_data[] = $data;
        return response()->json($profile_data);
    }


    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'sometimes|required|string|max:255',
            'model_car' => 'sometimes|required|string|max:255',
            'number_car' => 'sometimes|required|string|max:255',
            'color_car' => 'sometimes|required|string|max:255',
            'password' => 'sometimes|nullable|min:8',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        if ($request->has('name')) {
            $user->name = $request->input('name');

        }
        if ($request->has('email')) {
            $user->email = $request->input('email');
        }
        if ($request->has('phone')) {
            $user->phone = $request->input('phone');
        }
        if ($request->has('model_car')) {
            $user->Driver->model_car = $request->input('model_car');
        }
        if ($request->has('number_car')) {
            $user->Driver->number_car = $request->input('number_car');
        }
        if ($request->has('color_car')) {
            $user->Driver->color_car = $request->input('color_car');
        }
        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $user->save();
        return response()->json(['message' => 'Profile updated successfully']);
    }


    public function trip_history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|max:255',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $user = Auth::user();
        $driver = $user->Driver;
        // Retrieve all trips for the driver with an optional status filter
        $trips = $driver->Trip()
            ->with(['Reservation' => function ($query) {
                $query->with('user:id,name')
                    ->where('status', '!=', 'canceled');
            }])
            ->get()
            ->map(function ($trip) {
                // Replace user_id with the associated user's name
                $trip->reservation->each(function ($reservation) {
                    $reservation->user_name = $reservation->user->name;
                    unset($reservation->user);
                });
                return $trip;
            });
        // Create a new array with the desired structure
        $response = [];
        // Populate the array with the data from the trips collection
        foreach ($trips as $trip) {
            $response[] = [
                'id' => $trip->id,
                'from' => $trip->Path->from,
                'to' => $trip->Path->to,
                'driver_id' => $trip->Driver->name,
                'num_passenger' => $trip->num_passenger,
                'status' => $trip->status,
                'reservation' => $trip->reservation->toArray(),
            ];
        }
        // Return the JSON response
        return response()->json($response);
    }
    public function history_order_private_trip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|max:255',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $user = Auth::user();
        $driver = $user->Driver;
        $order_privates = Order_private::where('driver_id', $driver->id)->where('status',  $request->input('status'))->get();

        $response = [];
        // Populate the array with the data from the trips collection
        foreach ($order_privates as $order_private) {
            $response[] = [
                'id' => $order_private->id,
                'driver' => $order_private->Driver->user->name,
                'from' => $order_private->Private_trip->from,
                'to' => $order_private->Private_trip->to,
                'status' => $order_private->status,
                'price' => $order_private->price,

            ];
        }
        // Return the JSON response
        return response()->json($response);

    }
}
