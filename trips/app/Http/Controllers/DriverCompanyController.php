<?php

namespace App\Http\Controllers;

use App\Models\Driver_Company;
use App\Models\Bus;
use App\Models\Tickt;
use App\Models\Bus_Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class DriverCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $drivers = $user->Company->Driver_Company;

        $response = [];
        foreach ($drivers as $driver) {
            $response[] = [
                'id' => $driver->id,
                'name' => $driver->user->name,
                'email' => $driver->user->email,
                'phone' => $driver->user->phone,
                'status' => $driver->status,

            ];
        }

        return response()->json($response);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function driver_by_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|max:255',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $user = Auth::user();
        $drivers = $user->Company->Driver_Company->where('status', $request->input('status'));

        $response = [];
        foreach ($drivers as $driver) {
            $response[] = [
                'id' => $driver->id,
                'name' => $driver->user->name,
                'email' => $driver->user->email,
                'phone' => $driver->user->phone,
                'status' => $driver->status,
            ];
        }

        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function block_driver($id)
    {
        $user = Auth::user();
        $driver = $user->Company->Driver_Company->find($id);

        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }


        if ($driver->company_id != $user->Company->id) {
            return response()->json(['error' => 'You are not authorized to block this driver'], 403);
        }


        $driver->status = 'blocked'; // blocked
        $driver->save();

        return response()->json(['message' => 'Driver blocked successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver_Company $driver_Company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Driver_Company $driver_Company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver_Company $driver_Company)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver_Company $driver_Company)
    {
        //
    }

    public function my_trip()
    {
        // Get the authenticated driver's ID
        $driverId = auth()->user()->Driver_Company->id;

        // Get the buses associated with this driver
        $buses = Bus::where('driver__company_id', $driverId)->get();

        $trips = [];

        // Loop through each bus and retrieve the pending trips
        foreach ($buses as $bus) {
            $busId = $bus->id;
            $pendingTrips = Bus_Trip::where('bus_id', $busId)
                ->where('status', 'panding')
                ->with('comp_trip') // Eager load the comp_trip relationship
                ->get();

            // Add the pending trips to the array
            foreach ($pendingTrips as $trip) {
                $trips[] = [
                    'id_Bus_Trip' => $trip->id,
                    'from' => $trip->comp_trip->from,
                    'to' => $trip->comp_trip->to,
                    'status_bus' => $trip->status,
                    'status_trip' => $trip->comp_trip->status,
                    'type' => $trip->comp_trip->type, // Access the comp_trip value
                    'end_time' => $trip->comp_trip->end_time,
                    'start_time' => $trip->comp_trip->start_time,
                    'price' => $trip->comp_trip->price,
                ];
            }
        }

        // Return the custom JSON response
        return response()->json($trips);
    }

    public function ticket_trip(Request $request, $bus_trip_id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|integer|in:0,1,2',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $tickets = Tickt::where('bus__trip_id', $bus_trip_id)
        ->where('status', '!=', 'finished')
        ->get();
        if ($request->input('type') == 0) {
            $tickets = $tickets->where('type', 0);
        } elseif ($request->input('type') == 1) {
            $tickets = $tickets->where('type', 1);
        } elseif ($request->input('type') == 2) {
            $tickets = $tickets->whereIn('type', [0, 1]);
        }

        return response()->json($tickets);
    }

    public function get_QR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|string|max:255',
            'num_passenger' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'bus_trip_id' => 'required|integer',
            'type' => 'required|integer|in:0,1,2',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $busTrip = Bus_Trip::find($request->input('bus_trip_id'));
        if (!$busTrip) {
            return response()->json(['error' => 'Bus trip not found'], 404);
        }
        $driverId = $busTrip->bus->Driver_company->id;
        $currentDriverId = auth()->user()->Driver_Company->id; // assuming you have a driver_id column in your users table
        if ($driverId !== $currentDriverId) {

            return response()->json(['error' => 'You are not authorized to scan QR code for this bus trip'], 403);
        }

        $ticket = Tickt::find($request->input('ticket_id'));

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        if ($ticket->status !== 'panding') {
            return response()->json(['error' => 'Your ticket has expired'], 400);
        }

        $ticket->status = 'complete';
        $ticket->save();
        $tripType = 'Going';
        if ($ticket->type == 1) {
            $tripType = 'return';
        }
        $response[] = [
            'num_passenger' => $ticket->num_passenger,
            'status' => $ticket->status,
            'type' => $tripType,
            'price' => $ticket->price,

        ];
        return response()->json($response, 200);

    }
}
