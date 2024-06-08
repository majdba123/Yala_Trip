<?php

namespace App\Http\Controllers;

use App\Models\Subscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SubscriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subscriptions = Subscriptions::all();
        $subscriptionss = [];
        foreach($subscriptions as $x)
        {
            $data = [
                'company' => $x->company->user->name,
                'type' => $x->type,
                'price' =>  $x->price,
            ];
            $subscriptionss[] = $data;
        }

        return response()->json($subscriptionss);
    }


    public function by_company($company_id)
    {
        $subscriptions = Subscriptions::where('company_id', $company_id)->get();

        $subscriptionss = [];
        foreach($subscriptions as $x)
        {
            $data = [
                'company' => $x->company->user->name,
                'type' => $x->type,
                'price' =>  $x->price,
            ];
            $subscriptionss[] = $data;
        }
        return response()->json($subscriptionss);

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
            'type' => 'required|in:weekly,monthly,yearly',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $company_id=Auth::user()->company->id;

        $model = new Subscriptions();
        $model->company_id = $company_id;
        $model->type = $request->input('type');
        $model->price = $request->input('price');
        $model->save();

        return response()->json(['message' => 'Subscriptions created successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscriptions $subscriptions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscriptions $subscriptions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|in:weekly,monthly,yearly',
            'price' => 'sometimes|required|numeric',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $company_id = Auth::user()->company->id;
        $model = Subscriptions::find($id);
        if (!$model) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }
        if ($model->company_id !== $company_id) {
            return response()->json(['error' => 'You are not authorized to update this subscription'], 403);
        }
        if ($request->has('type')) {
            $model->type = $request->input('type');
        }
        if ($request->has('price')) {
            $model->price = $request->input('price');
        }
        $model->save();
        return response()->json(['message' => 'Subscription updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $company_id = Auth::user()->company->id;
        $model = Subscriptions::find($id);
        if (!$model) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }
        if ($model->company_id !== $company_id) {
            return response()->json(['error' => 'You are not authorized to delete this subscription'], 403);
        }
        $model->delete();
        return response()->json(['message' => 'Subscription deleted successfully']);
    }
}
