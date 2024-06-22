<?php

namespace App\Http\Controllers;

use App\Models\Driver_Company;
use App\Models\Bus_Trip;
use App\Models\Bus;
use App\Models\Tickt;
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
    public function show($id)
    {
        $companyId = auth()->user()->Company->id;
        $driver = Driver_Company::find($id);

        if (!$driver) {
            return response()->json([
                'message' => 'Driver not found',
            ], 404);
        }
        if ($driver->company->id != $companyId) {
            return response()->json([
                'message' => 'You do not have access to this driver',
            ], 403);
        }
        $data = [
            'id' => $driver->id,
            'name' => $driver->user->name,
            'email' => $driver->user->email,
            'phone' => $driver->user->phone,
            'status' => $driver->status,
        ];

        return response()->json($data);
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
                ->where('status', 'pending')
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
        $driver=Auth::user()->Driver_Company->id;
        $busTrips = Bus_Trip::find($bus_trip_id);
        if ($driver != $busTrips->bus->Driver_company->id ) {
            return response()->json(['error' => 'this ticket not for you  '], 403);

        }
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $tickets = Tickt::where('bus__trip_id', $bus_trip_id)
        ->where('status', 'pending')
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
        if ($ticket->status !== 'pending') {
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

    public function start_trip($bus_trip_id)
    {
        // Get the bus trip by ID
        $bus_trip = Bus_Trip::find($bus_trip_id);

        $driver_id=Auth::user()->Driver_Company->id;

        if (!$bus_trip) {
            // Handle the case where the bus trip is not found
            return response()->json(['error' => 'Bus trip not found'], 404);
        }
        // Check if the driver has the bus assigned to them
        if ($bus_trip->bus->Driver_company->id != $driver_id) {
            // Handle the case where the driver does not have the bus assigned
            return response()->json(['error' => 'You do not have permission to start this trip'], 403);
        }
        // Change the status of the bus trip to "running"
        $bus_trip->status = 'running';
        $bus_trip->save();

        // Get all tickets by bus ID and status "pending"
        $tickets = Tickt::where('bus__trip_id', $bus_trip_id)
            ->where('status', 'pending')
            ->where('type', 0)
            ->get();
            // Change the status of each ticket to "out"
        foreach ($tickets as $ticket) {
            $ticket->status = 'out';
            $ticket->save();
        }
        return response()->json(['message' => 'trip started succufully']);
    }

    public function finished_going_trip($bus_trip_id)
    {
                // Get the bus trip by ID
                $bus_trip = Bus_Trip::find($bus_trip_id);

                $driver_id=Auth::user()->Driver_Company->id;

                if (!$bus_trip) {
                    // Handle the case where the bus trip is not found
                    return response()->json(['error' => 'Bus trip not found'], 404);
                }
                // Check if the driver has the bus assigned to them
                if ($bus_trip->bus->Driver_company->id != $driver_id) {
                    // Handle the case where the driver does not have the bus assigned
                    return response()->json(['error' => 'You do not have permission to start this trip'], 403);
                }
                // Change the status of the bus trip to "running"
                $bus_trip->status = 'finished_going';
                $bus_trip->save();
                return response()->json(['message' => 'trip going finished succufully']);
    }

    public function start_trip_return($bus_trip_id)
    {
        // Get the bus trip by ID
        $bus_trip = Bus_Trip::find($bus_trip_id);

        $driver_id=Auth::user()->Driver_Company->id;

        if (!$bus_trip) {
            // Handle the case where the bus trip is not found
            return response()->json(['error' => 'Bus trip not found'], 404);
        }
        // Check if the driver has the bus assigned to them
        if ($bus_trip->bus->Driver_company->id != $driver_id) {
            // Handle the case where the driver does not have the bus assigned
            return response()->json(['error' => 'You do not have permission to start this trip'], 403);
        }
        // Change the status of the bus trip to "running"
        $bus_trip->status = 'return';
        $bus_trip->save();

        // Get all tickets by bus ID and status "pending"
        $tickets = Tickt::where('bus__trip_id', $bus_trip_id)
            ->where('status', 'pending')
            ->where('type', 1)
            ->get();
            // Change the status of each ticket to "out"
        foreach ($tickets as $ticket) {
            $ticket->status = 'out';
            $ticket->save();
        }
        return response()->json(['message' => 'trip_return started succufully']);
    }


    public function finished_return_trip($bus_trip_id)
    {
        // Get the bus trip by ID
        $bus_trip = Bus_Trip::find($bus_trip_id);

        $driver_id = Auth::user()->Driver_Company->id;

        if (!$bus_trip) {
            // Handle the case where the bus trip is not found
            return response()->json(['error' => 'Bus trip not found'], 404);
        }
        // Check if the driver has the bus assigned to them
        if ($bus_trip->bus->Driver_company->id!= $driver_id) {
            // Handle the case where the driver does not have the bus assigned
            return response()->json(['error' => 'You do not have permission to start this trip'], 403);
        }
        // Change the status of the bus trip to "finished"
        $bus_trip->status = 'finished';

        $bus_trip->save();
        $bus_trip->bus->Driver_company->status = 'available';
        $bus_trip->bus->Driver_company->save();


        // Get the company trip associated with this bus trip
        $comp_trip = $bus_trip->comp_trip;
        // Check if all bus trips associated with this company trip are finished
        $all_bus_trips_finished = $comp_trip->Bus_Trip->every(function ($bus_trip) {
            return $bus_trip->status == 'finished';
        });
        if ($all_bus_trips_finished) {
            // Change the status of the company trip to "finished"
            $comp_trip->status = 'finished';
            $comp_trip->save();

        }
        return response()->json(['message' => 'trip_return finished successfully']);
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        try {
            $bus_id = $user->Driver_Company->Bus->id;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Driver does not have a bus'], 404);
        }

        $busTrips = Bus_Trip::where('bus_id', $bus_id);

        $status = request()->input('status');
        if ($status) {
            $busTrips = $busTrips->where('status', $status);
        }

        $busTrips = $busTrips->get();

        if ($busTrips->isEmpty()) {
            return response()->json(['message' => 'No bus trips found'], 404);
        }

        $bookingData = [];

        foreach ($busTrips as $busTrip) {
            $totalPrice = Tickt::where('bus__trip_id', $busTrip->id)
            ->where('status', 'complete')
            ->sum('price');

            $data = [
                'from' => $busTrip->comp_trip->from,
                'to' => $busTrip->comp_trip->to,
                'tart_time' =>  $busTrip->comp_trip->start_time,
                'end_time' =>   $busTrip->comp_trip->end_time,
                'price' => $busTrip->comp_trip->price,
                'status_trip' =>$busTrip->comp_trip->status,
                'type' =>  $busTrip->comp_trip->type,
                'status_bus' =>$busTrip->status,
                'total_price' =>$totalPrice,
            ];

            $bookingData[] = $data;
        }

        return response()->json($bookingData);
    }

    public function trip_driver($id)
    {
        $driver = Driver_Company::find($id);
        $companyId = $driver->company->id;
        $bus_id = $driver->bus->id;
        $bustrip = Bus_Trip::where('bus_id', $bus_id)->get();
        $info = [];

        foreach ($bustrip as $bustrips) {
            if ($bustrips->comp_trip->company->id == $companyId) {
                $tickets = Tickt::where('bus__trip_id', $bustrips->id)
                    ->where('status', 'complete')
                    ->get();
                $total_profit = $tickets->sum('price');
                $data[] = [
                    'from' => $bustrips->comp_trip->from,
                    'to' => $bustrips->comp_trip->to,
                    'start_time' => $bustrips->comp_trip->start_time,
                    'end_time' => $bustrips->comp_trip->end_time,
                    'price' => $bustrips->comp_trip->price,
                    'type' => $bustrips->comp_trip->type,
                    'status_trip' => $bustrips->comp_trip->status,
                    'status_bus_trip' => $bustrips->status,
                    'total_profit' => $total_profit
                ];
                $info[] = $data;
            }
        }
        return response()->json($info);
    }
    public function all_bus_trip()
    {
        $driver = Auth::user()->Driver_Company->id;

        $diver_com = Driver_Company::find($driver);

        $diver_com->load('Bus.Bus_Trip.comp_trip','user'); // Load the relationships

        return response()->json($diver_com);
    }

}
