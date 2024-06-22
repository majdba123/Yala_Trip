<?php

namespace App\Http\Controllers;
use App\Models\Private_trip;
use App\Models\Order_private;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class OrderPrivateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function get_private_order_by_driver(Request $request ,$id)
    {
        $private_trip = Private_trip::find($id);
        if (!$private_trip) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'price' => 'required',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $user = Auth::user();
        $driver = $user->Driver;

        $private = new Order_private();
        $private->private_trip_id = $id;
        $private->driver_id = $driver->id;
        $private->price = $request->input('price');
        $private->save();
        return response()->json(['message' => 'your order created successfully'], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function get_order_private($id)
    {
        $private_trips = Order_private::where('private_trip_id', $id)->get();

        $tripData = [];
        foreach ($private_trips as $private_trip) {
            $data = [
                'id' => $private_trip->id,
                'driver' => $private_trip->Driver->user->name,
                'from' => $private_trip->Private_trip->from,
                'to' => $private_trip->Private_trip->to,
                'date' => $private_trip->Private_trip->date,
                'time' => $private_trip->Private_trip->time,
                'price' => $private_trip->price,
                'status' => $private_trip->status,
            ];
            $tripData[] = $data;
        }
        // Return the formatted private trip data to the driver
        return response()->json($tripData);

    }

    /**
     * Display the specified resource.
     */
    public function accept_order_private($id)
    {
        $order_private = Order_private::findOrFail($id);
        $user = Auth::user();
        $private_trip = Private_trip::findOrFail($order_private->private_trip_id);

        // Check if the user is the owner of the private trip
        if ($private_trip->user_id !== $user->id) {
            return response()->json(['error' => 'You are not authorized to accept this order'], 403);
        }

        // If the user is the owner, accept the order
        $order_private->status = 'accepted';
        $private_trip->status = 'complete';
        $private_trip->save();
        $order_private->save();

        // Update the status of all associated Order_private records to "out"
        $order_privates = Order_private::where('private_trip_id', $private_trip->id)->where('status', 'pending')->get();
        foreach ($order_privates as $order_private) {
            $order_private->status = 'out';
            $order_private->save();
        }

        return response()->json(['message' => 'Order accepted successfully'], 200);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function finished_oder_private($id)
    {
        $order_private = Order_private::findOrFail($id);
        $private_trip = Private_trip::findOrFail($order_private->private_trip_id);
        $user = Auth::user();
        $driver = $user->Driver;
        // Check if the authenticated user is the driver of the order_private
        if ($order_private->driver_id!== $driver->id) {
            return response()->json(['error' => 'You are not authorized to finish this order'], 403);
        }
        // If the user is the driver, update the status of the order_private to "finished"
        $order_private->status = 'finished';
        $order_private->save();
        $private_trip->status = 'finished';
        $private_trip->save();
        $user_owner=$order_private->Private_trip->user;
        $price = $order_private->price;

        // Check if the user has enough points
        if ($user_owner->point < $price) {
            return response()->json(['error' => 'Insufficient points'], 402);
        }

        // Subtract the price from the user's balance
        $user_owner->point -= $price;
        $user_owner->save();
        // Add the price to the driver's balance
        $user->point += $price;
        $user->save();
        return response()->json(['message' => 'Order finished successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order_private $order_private)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order_private $order_private)
    {
        //
    }
}
