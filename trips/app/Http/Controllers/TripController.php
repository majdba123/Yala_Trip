<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\breaking_Trip;
use App\Models\breaking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Reservation;
class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index_trip($id)
    {
        $trips = Trip::where('path_id', $id)
            ->where('status', 'panding')
            ->get();
        $tripData = [];
        foreach ($trips as $trip) {
            $trips = Reservation::where('trip_id', $trip->id)
            ->where('status', 'panding')
            ->sum('num_passenger');

            if ($trips > 0) {
                $remaining_passengers = $trip->num_passenger - $trips;
            } else {
                $remaining_passengers = $trip->num_passenger;
            }
            $breaks = $trip->breaking_Trip;
            $data = [
                'id' => $trip->id,
                'from' => $trip->Path->from,
                'to' => $trip->Path->to,
                'city' => $trip->Path->city,
                'price' => $trip->Path->price,
                'driver_name' => $trip->Driver->user->name,
                'lang' => $trip->Driver->user->lang,
                'lat' => $trip->Driver->user->lat,
                'car_model' => $trip->Driver->model_car,
                'number_car' => $trip->Driver->number_car,
                'num_passenger' => $trip->num_passenger,
                'remaining_number_of_passengers' => $remaining_passengers ,
                'status' => $trip->status,
                'breaks' => $breaks->sortBy('break.sorted')->map(function($breakk) {
                    return [
                        'id' => $breakk->id,
                        'sorted' => $breakk->break->sorted,
                        'name' => $breakk->break->name,
                        // add other break fields as needed
                    ];
                })->all()
            ];
            $tripData[] = $data;
        }
        return response()->json($tripData, 200);
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
        $validator = Validator::make($request->all(), [
            'path_id' => 'required|exists:paths,id',
            'num_passenger' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $user = Auth::user();
        $driver=$user->Driver;

        $existingTrip = Trip::where('driver_id', $driver->id)
        ->where('status', ['pandding', 'complete'])
        ->first();

        if ($existingTrip) {
        // If a trip already exists, return an error message
        return response()->json(['error' => 'driver already panding'], 409);
        }

        $trip = new Trip();
        $trip->path_id = $request->input('path_id');
        $trip->driver_id = $driver->id;
        $trip->num_passenger = $request->input('num_passenger');
        $trip->save();


        $breakings = breaking::where('path_id', $request->input('path_id'))->get();

        foreach ($breakings as $breaking) {
            // Create a new BreakingTrip record for each breaking
            $breakingTrip = new breaking_Trip();
            $breakingTrip->trip_id = $trip->id;
            $breakingTrip->breaking_id = $breaking->id;
            $breakingTrip->save();
        }

        return response()->json([
            'message' => 'trip Created ',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Trip $trip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Trip $trip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'path_id' => 'exists:paths,id',
            'num_passenger' => 'integer',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        // Get the authenticated user and their driver
        $user = Auth::user();
        $driver = $user->Driver;
        // Use the updateOrCreate method to update or create a new trip
        $trip = Trip::find($id);
        if (!$trip) {
            // If the trip doesn't exist, return an error message
            return response()->json(['error' => 'Trip not found'], 404);
        }
        if ($trip->driver_id !== $driver->id) {
            return response()->json(['error' => 'you are not owner 0f this trip '], 401);
        }
        if ($request->has('path_id')) {
            // If the path_id field is present in the request, update it
            $trip->path_id = $request->input('path_id');
        }
        if ($request->has('num_passenger')) {
            // If the num_passenger field is present in the request, update it
            $trip->num_passenger = $request->input('num_passenger');
        }
        $trip->save();
        // Return a success message
        return response()->json([
            'message' => 'Trip updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        // Use the find method to retrieve the existing trip with the given id
        $trip = Trip::find($id);
        if (!$trip) {
            // If the trip doesn't exist, return an error message
            return response()->json(['error' => 'Trip not found'], 404);
        }
        // Get the authenticated user and their driver
        $user = Auth::user();
        $driver = $user->Driver;
        // Check if the authenticated user is the owner of the trip
        if ($trip->driver_id !== $driver->id) {
            return response()->json(['error' => 'you are not owner 0f this trip'], 401);
        }
        // Delete the trip
        $trip->delete();
        // Return a success message
        return response()->json([
            'message' => 'Trip deleted successfully',
        ]);
    }
}
