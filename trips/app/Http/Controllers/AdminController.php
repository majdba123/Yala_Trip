<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Company;
use App\Models\Comp_trip;
use App\Models\Driver_Company;
use App\Models\Bus_Trip;
use App\Models\Trip;
use App\Models\Reservation;
use App\Models\Private_trip;
use App\Models\Driver;
use App\Models\Contuct_us;


class AdminController extends Controller
{
    public function all_user()
    {
        $users = User::all(); // assuming you have a User model
        return response()->json($users);
    }


    public function show_user($id)
    {
        $user = User::find($id); // assuming you have a User model
        if ($user) {
            return response()->json($user);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }


    public function block_user($id)
    {
        $user = User::find($id); // assuming you have a User model
        if ($user) {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully'], 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function all_company()
    {
        $companies = Company::all(); // assuming you have a User model
        $company_info=[];
        foreach($companies as $companiess)
        {
            $companyTrips = Comp_trip::where('company_id', $companiess->id)
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


            $all_driver = Driver_Company::where('company_id' ,$companiess->id )->count();
            $finished_trip =$companyTrips->count();
            $panding_trip =$companyTrips->where('status' , 'pending')->count();

            $num_subscription = Company::find($companiess->id)->Subscriptions;
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
                "name" => $companiess->user->name,
                "email" => $companiess->user->email,
                "point" => $companiess->user->point,
                "phone" => $companiess->user->phone,
                "total_profit_trips" => $totalPrice,
                "num_driver" => $all_driver,
                "num_finished_trip" => $finished_trip,
                "num_current_trip" =>$panding_trip,
                "num_suscription_active" => $count_active,
                "num_suscription_finished" => $count_expired,
                "total_profit_from_suscripe" => $total_profit,
                "total_driver" => Company::find($companiess->id)->Driver_Company->count(),
                "total_bus" => Company::find($companiess->id)->Bus->count(),
                "total_all_trip" => Company::find($companiess->id)->Comp_trip->count(),
                "count_user" => $count_user,
            ];

            $company_info[]=$data;
        }

        return response()->json($company_info);
    }

    public function company_info($id)
    {
        $company = Company::find($id);
        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        $companyTrips = Comp_trip::where('company_id', $company->id)
            ->where('status', 'finished')
            ->get();

        $totalPrice = 0;
        foreach ($companyTrips as $companyTrip) {
            if ($companyTrip->has('Bus_Trip')) {
                $busTrips = $companyTrip->Bus_Trip()->where('status', 'finished')->get();

                if ($busTrips->isNotEmpty()) {
                    foreach ($busTrips as $busTrip) {
                        $tickets = $busTrip->Tickt()->where('status', 'complete')->get();
                        if ($tickets->isNotEmpty()) {
                            $totalPrice += $tickets->sum('price');
                        }
                    }
                }
            }
        }

        $all_driver = Driver_Company::where('company_id', $company->id)->count();
        $finished_trip = $companyTrips->count();
        $panding_trip = $companyTrips->where('status', 'pending')->count();

        $num_subscription = $company->Subscriptions;
        $count_active = 0;
        $count_expired = 0;
        $total_profit = 0;
        $count_user = 0;
        foreach ($num_subscription as $subscription) {
            $count_active += $subscription->user_subscription->where('status', 'active')->count();
        }

        foreach ($num_subscription as $subscription) {
            $count_expired += $subscription->user_subscription->where('status', 'expired')->count();
        }

        foreach ($num_subscription as $subscription) {
            $total_profit += $subscription->price * $subscription->user_subscription->count();
        }

        foreach ($num_subscription as $subscription) {
            $count_user += $subscription->user_subscription->count();
        }

        $data = [
            "name" => $company->user->name,
            "email" => $company->user->email,
            "point" => $company->user->point,
            "phone" => $company->user->phone,
            "total_profit_trips" => $totalPrice,
            "num_driver" => $all_driver,
            "num_finished_trip" => $finished_trip,
            "num_current_trip" => $panding_trip,
            "num_suscription_active" => $count_active,
            "num_suscription_finished" => $count_expired,
            "total_profit_from_suscripe" => $total_profit,
            "total_driver" => $company->Driver_Company->count(),
            "total_bus" => $company->Bus->count(),
            "total_all_trip" => $company->Comp_trip->count(),
            "count_user" => $count_user,
        ];

        return response()->json($data);
    }

    public function delete_company($id)
    {
        $company = Company::find($id);
        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }
        $company->delete();
        return response()->json(['message' => 'Company deleted successfully'], 200);

    }

    public function all_driver()
    {
        $driver = Driver::all(); // assuming you have a User model
        $driver_INFO=[];
        foreach($driver as $drivers)
        {
             $diver_in= $drivers->with('Trip','Order_private');
             $driver_INFO[]=$diver_in;
        }
        return response()->json($driver_INFO);

    }

    public function driver_info($id)
    {
        $driver = Driver::find($id);
        if (!$driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }
        $driver_info = $driver->load('Trip', 'Order_private');
        return response()->json($driver_info);
    }

    public function delete_driver($id)
    {
        $Driver = Driver::find($id);
        if (!$Driver) {
            return response()->json(['error' => 'Driver not found'], 404);
        }
        $Driver->delete();
        return response()->json(['message' => 'Driver deleted successfully'], 200);

    }


    public function all_trip()
    {
        $Trip = Trip::all(); // assuming you have a User model
        $driver_INFO=[];
        foreach($Trip as $drivers)
        {
             $diver_in= $drivers->with('Path','Driver','Reservation','Rating' ,'breaking_Trip');
             $driver_INFO[]=$diver_in;
        }
        return response()->json($driver_INFO);

    }

    public function trip_info($id)
    {
        $trip = Trip::find($id);
        if (!$trip) {
            return response()->json(['error' => 'Trip not found'], 404);
        }
        $trip_info = $trip->load('Path', 'Driver', 'Reservation', 'Rating', 'breaking_Trip');
        return response()->json($trip_info);
    }


    public function all_reservation()
    {
        $Reservation = Reservation::all(); // assuming you have a User model
        $driver_INFO=[];
        foreach($Reservation as $Reservations)
        {
             $Reservations_inf= $Reservations->with('user','Trip','break');
             $driver_INFO[]=$Reservations_inf;
        }
        return response()->json($driver_INFO);

    }


    public function reservation_info($id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], 404);
        }
        $reservation_info = $reservation->load('user', 'Trip', 'break');
        return response()->json($reservation_info);
    }


    public function all_private_trip()
    {
        $Private_trip = Private_trip::all(); // assuming you have a User model
        $driver_INFO=[];
        foreach($Private_trip as $Private_trips)
        {
             $Reservations_inf= $Private_trips->with('user','Order_private');
             $driver_INFO[]=$Reservations_inf;
        }
        return response()->json($driver_INFO);

    }

    public function private_trip_info($id)
    {
        $private_trip = Private_trip::find($id);
        if (!$private_trip) {
            return response()->json(['error' => 'Private trip not found'], 404);
        }
        $private_trip_info = $private_trip->load('user', 'Order_private');
        return response()->json($private_trip_info);

    }

    public function contuct_as(Request $request)
    {
        $contacts = Contuct_us::query();

        if ($request->has('status')) {
            $contacts->where('status', $request->input('status'));
        }

        $contacts = $contacts->get();

        return response()->json($contacts);
    }

    public function change_status_contuct($id)
    {
        $contact = Contuct_us::find($id);
        if ($contact) {
            $contact->status = 'complete';
            $contact->save();
            return response()->json(['message' => 'Contact status updated successfully']);
        } else {
            return response()->json(['error' => 'Contact not found'], 404);
        }
    }
}
