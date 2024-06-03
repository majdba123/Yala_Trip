<?php

namespace App\Http\Controllers;

use App\Models\Driver_Company;
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
}
