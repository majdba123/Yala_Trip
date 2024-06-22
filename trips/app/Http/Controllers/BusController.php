<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Company;
use App\Models\Driver_Company;
class BusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()

    {

        $user = Auth::user();

        $company = $user->Company->id;
        $buses = Bus::where('company_id', $company)->get();
        return response()->json([
            'buses' => $buses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function bus_by_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $user = Auth::user();
        $company = $user->Company->id;

        $status = $request->input('status');

        $buses = Bus::where('company_id', $company)
                    ->where('status', $status)
                    ->get();

        return response()->json([
            'buses' => $buses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'integer|digits_between:1,10|unique:cars,number',
            'num_passenger' => 'integer|digits_between:1,10',
            'driver__company_id' => 'integer|exists:drivers,company_id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $user = Auth::user();
        $company=$user->Company->id;

        $driverCompany = Driver_Company::find($request->input('driver__company_id'));

        if (!$driverCompany) {
            return response()->json(['error' => 'Driver company not found'], 404);
        }
        if ($driverCompany->company_id != $company) {
            return response()->json(['error' => 'Driver company does not belong to the same company'], 403);
        }

        if ($driverCompany->status != 'pending') {
            return response()->json(['error' => 'Driver company status is not pending'], 403);
        }


        $bus = new Bus();
        $bus->number = $request->input('number');
        $bus->company_id =$company;
        $bus->num_passenger = $request->input('num_passenger');
        $bus->driver__company_id = $request->input('driver__company_id');
        $bus->status = 'complete';
        $bus->save();
        $driverCompany->status = 'available';
        $driverCompany->save();

        return response()->json([
            'message' => 'Bus created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();
        $company = $user->Company->id;
        $bus = Bus::with('company', 'Driver_Company')->find($id);
        if ($bus && $bus->company->id == $company) {
            return response()->json([
                'bus' => $bus,
            ]);
        } else {
            return response()->json([
                'essage' => 'You are not the owner of this bus',
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bus $bus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'sometimes|integer',
            'num_passenger' => 'sometimes|integer',
            'driver__company_id' => 'sometimes|integer|exists:drivers,company_id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $user = Auth::user();
        $company = $user->Company->id;
        $bus = Bus::with('company')->find($id);
        if ($bus && $bus->company_id == $company) {
            if ($request->has('number')) {
                $bus->number = $request->input('number');
            }
            if ($request->has('num_passenger')) {
                $bus->num_passenger = $request->input('num_passenger');
            }
            if ($request->has('driver__company_id')) {
                $driverCompany = Driver_Company::find($request->input('driver__company_id'));
                if (!$driverCompany) {
                    return response()->json(['error' => 'Driver company not found'], 404);
                }
                if ($driverCompany->status!= 'pending') {
                    return response()->json(['error' => 'Driver company status is not pending'], 403);
                }
                if ($driverCompany->company_id!= $company) {
                    return response()->json(['error' => 'Driver company does not belong to the same company'], 403);
                }
                $bus->driver__company_id = $request->input('driver__company_id');
                $driverCompany->status = 'available';
                $driverCompany->save();
            }
            $bus->save();

            return response()->json([
                'message' => 'Bus updated successfully',
            ]);
        } else {
            return response()->json([
                'message' => 'You are not the owner of this bus',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $company = $user->Company->id;
        $bus = Bus::with('company')->find($id);
        if ($bus && $bus->company_id == $company) {
            $driver = $bus->Driver_company;
            // Update the driver status to 'pending'
            $driver->status = 'pending';
            $driver->save();
            $bus->delete();
            return response()->json([
                'message' => 'Bus deleted successfully',
            ]);
        } else {
            return response()->json([
                'message' => 'You are not the owner of this bus',
            ]);
        }
    }
}
