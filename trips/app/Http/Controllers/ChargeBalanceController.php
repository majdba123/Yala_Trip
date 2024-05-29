<?php

namespace App\Http\Controllers;

use App\Models\Charge_balance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ChargeBalanceController extends Controller
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
    public function store(Request $request)
    {
        try {
            $imageName = Str::random(32).".".$request->imge->getClientOriginalExtension();
            $user = auth()->user();
            // Create Post
            Charge_balance::create([
                'user_id' => $user->id,
                'imge' => $imageName,
                'balance' => $request->balance
            ]);

            // Save Image in Storage folder
            Storage::disk('public')->put($imageName, file_get_contents($request->imge));

            // Return Json Response
            return response()->json([
                'message' => "Post successfully created."
            ],200);
        } catch (\Exception $e) {
            // Return Json Response
            return response()->json([
                'message' => "Something went really wrong!"
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Charge_balance $charge_balance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Charge_balance $charge_balance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Charge_balance $charge_balance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Charge_balance $charge_balance)
    {
        //
    }
}
