<?php

namespace App\Http\Controllers;

use App\Models\Private_trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class PrivateTripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function driver_order()
    {
        // Retrieve all private trips with a pending status
        $private_trips = Private_trip::where('status', 'panding')->get();
        // Format the private trip data
        $tripData = [];
        foreach ($private_trips as $private_trip) {
            $data = [
                'id' => $private_trip->id,
                'user' => $private_trip->user->name,
                'from' => $private_trip->from,
                'to' => $private_trip->to,
                'date' => $private_trip->date,
                'time' => $private_trip->time,
                'status' => $private_trip->status,
            ];
            $tripData[] = $data;
        }
        // Return the formatted private trip data to the driver
        return response()->json($tripData);
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
            'from' => 'required',
            'to' => 'required',
            'date' => 'required',
            'time' => 'required',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $user = Auth::user();

        $private = new Private_trip();
        $private->user_id = $user->id;
        $private->from = $request->input('from');
        $private->to = $request->input('to');
        $private->date = $request->input('date');
        $private->time = $request->input('time');
        $private->save();
        return response()->json(['message' => 'Private trip created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Private_trip $private_trip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        $private_trip = Private_trip::findOrfail($id);
        $validator = Validator::make($request->all(), [
            'from' => 'sometimes|required',
            'to' => 'sometimes|required',
            'date' => 'sometimes|required',
            'time' => 'sometimes|required',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        if ($request->has('from')) {
            $private_trip->from = $request->input('from');
        }
        if ($request->has('to')) {
            $private_trip->to = $request->input('to');
        }
        if ($request->has('date')) {
            $private_trip->date = $request->input('date');
        }
        if ($request->has('time')) {
            $private_trip->time = $request->input('time');
        }

        $private_trip->save();
        return response()->json(['message' => 'Private trip updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $private_trip = Private_trip::findOrfail($id);
        $user = Auth::user();
        if ($private_trip->user_id !== $user->id) {
            return response()->json(['error' => 'you are not owner of this trip'], 401);
        }
        $private_trip->delete();
        return response()->json(['message' => 'Private trip deleted successfully'], 200);

    }
}
