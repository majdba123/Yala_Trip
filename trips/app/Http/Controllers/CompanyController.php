<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Comp_trip;
use App\Models\Driver_Company;
use App\Models\Bus_Trip;


class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
        ], [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'email.unique' => 'Email has already been taken',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters long',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 422);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $id= $user->id;
        $company = Company::create([
            'user_id' => $id,
        ]);


        return response()->json([
            'message' => 'company Created ',
        ]);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function register_driver(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
        ], [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'email.unique' => 'Email has already been taken',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters long',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 422);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
        $x =Auth::user();
        $company = $x->Company->id;

        $id= $user->id;
        $driver = Driver_Company::create([
            'user_id' => $id,
            'company_id' => $company,
        ]);


        return response()->json([
            'message' => 'Driver Created ',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        //
    }


    public function company(Request $request)

    {

        if ($request->has('name')) {
            $companies = Company::whereHas('user', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            })->get();
        } else {
            $companies = Company::all();
        }

        $companies1 = [];
        foreach($companies as $x)
        {
            $data = [
                'company_name' => $x->user->name,
                'email' => $x->user->email,
                'phone' =>  $x->user->phone,
            ];
            $companies1[] = $data;
        }

        return response()->json($companies1);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function dashboard(Request $request)
    {

        $companyId = auth()->user()->Company->id;


        // Get the company trips that belong to this company
        $companyTrips = Comp_trip::where('company_id', $companyId)
        ->where('status', 'finished')
        ->get();
        if($companyTrips ){
            foreach($companyTrips  as $companyTripss)
            {
                if ($companyTripss->has('Bus_Trip')) {
                    $busTrips = $companyTripss->Bus_Trip()->where('status', 'finished')->get();

                    if ($busTrips->isNotEmpty()) {
                        $totalPrice = 0;
                        foreach($busTrips as $bus_tripp)
                        {
                            $tickets = $bus_tripp->Tickt()->where('status', 'complete')->get();
                            if($tickets->isNotEmpty())
                            {
                                $totalPrice += $tickets->sum('price');
                            }
                        }
                        // do something with $totalPrice
                    }
                }
            }
        }


        $all_driver = Driver_Company::where('company_id' ,$companyId )->count();
        $finished_trip =$companyTrips->count();
        $panding_trip =$companyTrips->where('status' , 'pending')->count();

        $num_subscription = Company::find($companyId)->Subscriptions;
        $count_active = 0;
        $count_expired = 0;
        $total_profit = 0;
        $count_user = 0;
        foreach($num_subscription as $subscription) {
            $count_active += $subscription->user_subscription->where('status' , 'active')->count();
        }

        foreach($num_subscription as $subscription) {
            $count_expired += $subscription->user_subscription->where('status' , 'expired')->count();
        }

        foreach($num_subscription as $subscription) {
            $total_profit += $subscription->price * $subscription->user_subscription->count();
        }

        foreach($num_subscription as $subscription) {
            $count_user += $subscription->user_subscription->count();
        }

        $data = [
            "total_profit_trips" => $totalPrice,
            "num_driver" => $all_driver,
            "num_finished_trip" => $finished_trip,
            "num_current_trip" =>$panding_trip,
            "num_suscription_active" => $count_active,
            "num_suscription_finished" => $count_expired,
            "total_profit_from_suscripe" => $total_profit,
            "total_driver" => Company::find($companyId)->Driver_Company->count(),
            "total_bus" => Company::find($companyId)->Bus->count(),
            "total_all_trip" => Company::find($companyId)->Comp_trip->count(),
            "count_user" => $count_user,
        ];

        return response()->json($data);

    }
}
