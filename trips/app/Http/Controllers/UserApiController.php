<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Private_trip;
use App\Models\Order_private;
use App\Models\Tickt;
use App\Models\user_subscription;



class UserApiController extends Controller
{
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

        return response()->json([
            'message' => 'User Created ',
        ]);
    }



    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email'=>'required|string|email',
            'password'=>'required|min:8'
        ], [
            'email.required' => 'Email is required',
            'email.email' => 'Email is invalid',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters long',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 422);
        }

        $user = User::where('email',$request->input('email'))->first();

        if(!$user || !Hash::check($request->input('password'),$user->password)){
            return response()->json([
                'message' => 'Invalid Credentials'
            ],401);
        }

        $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;

        return response()->json([
            'access_token' => $token,
        ]);
    }




    public function logout(){
        auth()->user()->tokens()->delete();

        return response()->json([
          "message"=>"logged out"
        ]);
    }

    public function info()
    {
        $user = Auth::user();
        $password=$user->password;
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'point' =>  $user->point,
            'phone' =>  $user->phone,
            'lat' =>  $user->lat,
            'lang' =>  $user->lang,
        ];
        $profile_data[] = $data;
        return response()->json($profile_data);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'sometimes|required|string|max:255',
            'password' => 'sometimes|nullable|min:8',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        if ($request->has('name')) {
            $user->name = $request->input('name');

        }
        if ($request->has('email')) {
            $user->email = $request->input('email');
        }
        if ($request->has('phone')) {
            $user->phone = $request->input('phone');
        }
        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $user->save();
        return response()->json(['message' => 'Profile updated successfully']);
    }


    public function history_order_private_trip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|max:255',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $user = Auth::user();

        $private_trips = $user->Private_trip()->where('status', $request->input('status'))->get();
        $response = [];
        // Populate the array with the data from the trips collection
        foreach ($private_trips as $private_trip) {
            $order_privates = $private_trip->Order_private()->where('status', $request->input('status'))->get();
            foreach ($order_privates as $order_private) {
                $response[] = [
                    'id' => $order_private->id,
                    'driver' => $order_private->Driver->user->name,
                    'from' => $private_trip->from,
                    'to' => $private_trip->to,
                    'status' => $order_private->status,
                    'price' => $order_private->price,
                ];
            }
        }
        // Return the JSON response
        return response()->json($response);
    }

    public function history_unversity()
    {
        $user_id = auth()->user()->id; // Get the current user's ID
        $tickets = Tickt::where('user_id', $user_id)->get(); // Retrieve all tickets associated with the current user
        $tickets->load('Bus_Trip.comp_trip' , 'Bus_Trip.bus'); // Load the Bus_Trip relationship and its nested comp_trip relationship
        return response()->json($tickets);
    }

    public function subscription()
    {
        $user_id = auth()->user()->id; // Get the current user's ID
        $subscriptions = user_subscription::where('user_id', $user_id)->with('subscriptions')->get(); // Retrieve all subscriptions associated with the current user
        return response()->json($subscriptions);
    }

}
