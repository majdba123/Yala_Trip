<?php

namespace App\Http\Controllers;

use App\Models\Contuct_us;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ContuctUsController extends Controller
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
        $validator = Validator::make($request->all(), [
            'comment' => 'sometimes|required|string',
            'email' => 'sometimes|required|email',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        $user_id=auth::user()->id;
        $contuct = new Contuct_us();
        $contuct->comment = $request->input('comment');
        $contuct->email = $request->input('email');
        $contuct->user_id = $user_id;
        $contuct->save();
        return response()->json(['message' => 'Thank you for contacting us!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contuct_us $contuct_us)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contuct_us $contuct_us)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'sometimes|required|string',
            'email' => 'sometimes|required|email',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $contuct = Contuct_us::find($id);
        if (!$contuct) {
            return response()->json(['error' => 'Contuct not found'], 404);
        }

        $user_id = auth::user()->id;
        if ($contuct->user_id!== $user_id) {
            return response()->json(['error' => 'You are not authorized to update this contuct'], 403);
        }

        if ($request->has('comment')) {
            $contuct->comment = $request->input('comment');
        }

        if ($request->has('email')) {
            $contuct->email = $request->input('email');
        }

        $contuct->save();

        return response()->json(['message' => 'Contuct updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $contuct = Contuct_us::find($id);
        if (!$contuct) {
            return response()->json(['error' => 'Contuct not found'], 404);
        }
        $user_id = auth::user()->id;
        if ($contuct->user_id!== $user_id) {
            return response()->json(['error' => 'You are not authorized to delete this contuct'], 403);
        }
        $contuct->delete();
        return response()->json(['message' => 'Contuct deleted successfully']);
    }

}
