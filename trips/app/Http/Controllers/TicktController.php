<?php

namespace App\Http\Controllers;

use App\Models\Tickt;
use App\Models\user_subscription;
use Illuminate\Http\Request;
use App\Models\Bus_Trip;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class TicktController extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'num_passenger' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:0,1',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $busTripId = $id; // assuming $id is the bus_trip_id
        $requestedType = $request->input('type');
        if ($requestedType == 1) {
            $busTrip = Bus_Trip::find($busTripId);
            if ($busTrip->comp_trip->type!= 1) {
                return response()->json(['error' => 'Sorry, this trip only goes for company trips with type 1'], 422);

            }}elseif($requestedType == 0){
                $busTrip = Bus_Trip::find($busTripId);
                if ($busTrip->status == 'running') {
                    return response()->json(['error' => 'The trip has already started'], 422);
                }
            }
        $requestedNumPassenger = $request->input('num_passenger');

        // Get all tickets with same type and bus_trip_id
        $tickets = Tickt::where('type', $requestedType)
            ->where('bus__trip_id', $busTripId)
            ->where('status',  ['panding' , 'complete'])
            ->get();
        // Calculate the total number of passengers for the same type and bus_trip_id
        $totalNumPassenger = $tickets->sum('num_passenger') ?? 0;

        // Get the bus capacity from the bus_trip table
        $busTrip = Bus_Trip::find($busTripId);
        if($busTrip->status == 'return')
        {
            return response()->json([
                'message' => 'this trip will be finished soon sorry',
            ]);
        }
        $price= $busTrip->comp_trip->price;
        $busCapacity = $busTrip->bus->num_passenger;
        $total_price1= $price * $requestedNumPassenger;

        $user = auth()->user();

        $company_subscription = $busTrip->comp_trip->company->Subscriptions;
        $company_subscription_ids = $company_subscription->pluck('id')->all();

        $hasSubscription = user_subscription::where('user_id', $user->id)
            ->whereIn('subscriptions_id', $company_subscription_ids)
            ->where('status', 'active')
            ->first();



        // Check if the total number of passengers + requested number of passengers is less than or equal to the bus capacity
        if ($totalNumPassenger + $requestedNumPassenger <= $busCapacity) {
            // Store the ticket
            if(!$hasSubscription){
                if ($user->point < $total_price1) {
                    // User does not have enough points
                    return response()->json([
                        'message' => 'You do not have enough points to make this reservation',
                    ]);
                }else{
                    $user->point -= $total_price1;
                    $user->save();
                    // Update driver points
                    $company = $busTrip->comp_trip->company->user;
                    $company->point += $total_price1;
                    $company->save();
                }
            }



            $user=Auth::user();
            $ticket = new Tickt();
            $ticket->bus__trip_id  = $busTripId;
            $ticket->user_id  = $user->id;
            $ticket->type = $requestedType;
            $ticket->num_passenger = $requestedNumPassenger;
            $ticket->price = $total_price1;
            $ticket->save();

            $updatedTotalNumPassenger = Tickt::where('bus__trip_id', $busTripId)
            ->where('status', ['panding' , 'complete'])
            ->sum('num_passenger');

            $companyTripType = Bus_Trip::find($busTripId)->comp_trip->type;
            if ($companyTripType == 1) {
                // If type is 1, use 2 * busCapacity
                if ($updatedTotalNumPassenger == 2 * $busCapacity) {
                    // Update the bus trip status to complete
                    $busTrip = Bus_Trip::find($busTripId);
                    if ($busTrip->status != 'running') {
                        $busTrip->status = 'complete';
                        $busTrip->save();
                    }
                }
            } elseif ($companyTripType == 0) {
                // If type is 0, use busCapacity without multiplying by 2
                if ($updatedTotalNumPassenger == $busCapacity) {
                    // Update the bus trip status to complete
                    $busTrip = Bus_Trip::find($busTripId);
                    if ($busTrip->status != 'running') {
                        $busTrip->status = 'complete';
                        $busTrip->save();
                    }
                }}
            $data = [
                'user' => $ticket->user->name,
                'ticket_id' => $ticket->id,
                'bus_trip_id' => $busTripId,
                'num_passenger' =>  $ticket->num_passenger,
                'price' =>  $ticket->price,
                'status' =>  $ticket->status ?: 'panding',
                'type' =>  $ticket->type,
            ];

            $bookingData[] = $data;
            return response()->json($bookingData);

            return response()->json(['message' => 'Ticket stored successfully'], 201);

        }else{
            return response()->json(['message' => 'no seat available'], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tickt $tickt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tickt $tickt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tickt $tickt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tickt $tickt)
    {
        //
    }
}
