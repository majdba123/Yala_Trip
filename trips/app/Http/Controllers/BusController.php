<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Company;
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
            'number' => 'required|integer',
            'num_passenger' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $user = Auth::user();
        $company=$user->Company->id;

        $bus = new Bus();
        $bus->number = $request->input('number');
        $bus->company_id =$company;
        $bus->num_passenger = $request->input('num_passenger');
        $bus->save();

        return response()->json([
            'message' => 'bus created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bus $bus)
    {
        //
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
            'number' => 'integer',
            'num_passenger' => 'integer',
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
