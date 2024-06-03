<?php

namespace App\Http\Controllers;

use App\Models\Comp_trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Driver_Company;
use App\Models\Bus_Trip;
class CompTripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $compTripsQuery = Comp_trip::with(['Bus_Trip' => function ($query) {
            $query->where('status', 'panding');
        }]);

        if ($request->has('company_id')) {
            $compTripsQuery->where('company_id', $request->input('company_id'));
        }

        if ($request->has('from')) {
            $compTripsQuery->where('from', $request->input('from'));
        }

        if ($request->has('to')) {
            $compTripsQuery->where('to', $request->input('to'));
        }

        $compTrips = $compTripsQuery->get();

        $tripData = [];
        foreach ($compTrips as $trip) {
            $data = [
                'id' => $trip->id,
                'company_id' => $trip->company_id,
                'from' => $trip->from,
                'to' => $trip->to,
                'price' => $trip->price,
                'start_time' => $trip->start_time,
                'end_time' => $trip->end_time,
                'status' => $trip->status,
                'bus_trips' => $trip->Bus_Trip->map(function ($busTrip) {
                    return [
                        'id' => $busTrip->id,
                        'company' => $busTrip->comp_trip->company->user->name,
                        'driver_name' => $busTrip->bus->Driver_company->user->name,
                        'lang' => $busTrip->bus->Driver_company->user->lang,
                        'lat' => $busTrip->bus->Driver_company->user->lat,
                        'number_bus' => $busTrip->bus->number,
                        'number_passenger' => $busTrip->bus->num_passenger,
                    ];
                })->all(),
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
            'from' => 'sometimes|required|string|max:255',
            'to' => 'sometimes|required|string|max:255',
            'start_time' => 'sometimes|required|string|max:255',
            'end_time' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|integer|in:0,1',
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $user = Auth::user();
        $company = $user->Company->id;
        $type = $request->input('type', 0);
        $companyTrip = Comp_trip::create([
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'price' => $request->input('price'),
            'type' => $type,
            'company_id' => $company,
        ]);

        foreach ($request->input('driver_ids') as $driverId) {
            $driver = Driver_Company::find($driverId);
            if ($driver) {
                if ($driver->status !== 'available') {
                    return response()->json(['error' => 'Driver is not available'], 422);
                }
                $busId = $driver->Bus->id;
                Bus_Trip::create([
                    'comp_trip_id' => $companyTrip->id,
                    'bus_id' => $busId,
                ]);
                $driver->update(['status' => 'complete']);
            } else {
                return response()->json(['error' => 'Invalid driver ID'], 422);
            }
        }
        return response()->json([
            'message' => 'CompanyTrip created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comp_trip $comp_trip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comp_trip $comp_trip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'from' => 'sometimes|required|string|max:255',
            'to' => 'sometimes|required|string|max:255',
            'start_time' => 'sometimes|required|string|max:255',
            'end_time' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|integer|in:0,1',
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $user = Auth::user();
        $company = $user->Company->id;
        $companyTrip = Comp_trip::find($id);

        if (!$companyTrip || $companyTrip->company->id != $company) {
            return response()->json(['error' => 'Company trip not found or not owned by the user'], 404);
        }

        $companyTrip->update([
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'price' => $request->input('price'),
        ]);
        if ($request->has('type')) {
            $companyTrip->update([
                'type' => $request->input('type'),
            ]);
        }
        if ($request->has('driver_ids'))
        {
            Bus_Trip::where('comp_trip_id', $companyTrip->id)->get()->each(function ($busTrip)
            {
                $driver = $busTrip->bus->Driver_company;
                if ($driver) {
                    $driver->update(['status' => 'available']);
                    $busTrip->delete();
                }
            });
            foreach ($request->input('driver_ids') as $driverId) {
                $driver = Driver_Company::find($driverId);
                if (!$driver) {
                    return response()->json(['error' => 'Invalid driver ID'], 422);
                }
                if ($driver->status == 'available') {
                    $busId = $driver->Bus->id;
                    Bus_Trip::create([
                        'comp_trip_id' => $companyTrip->id,
                        'bus_id' => $busId,
                    ]);
                    $driver->update(['status' => 'complete']);
                }elseif ($driver->status == 'complete') {
                    return response()->json([
                        'message' => 'driver can not update because the driver complete',
                    ]);
                }else{
                    return response()->json([
                        'message' => 'driver can not update because the driver panding',
                    ]);
                }
            }
        }
        return response()->json([
            'message' => 'Company trip updated successfully',
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comp_trip $comp_trip)
    {

    }
}
