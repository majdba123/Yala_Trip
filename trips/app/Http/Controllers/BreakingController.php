<?php

namespace App\Http\Controllers;

use App\Models\breaking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class BreakingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breakings = Breaking::all();
        return response()->json($breakings);
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
            'sorted' => 'required|in:asc,desc',
            'name' => 'required|string|max:255',
        ], [
            'sorted.in' => 'Invalid sorted direction. Please use asc or desc.',
            'name.max' => 'Name cannot be longer than 255 characters.',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }

        $breaking = new breaking();
        $breaking->path_id = $id;
        $breaking->sorted = $request->input('sorted');
        $breaking->name = $request->input('name');
        $breaking->save();
        return response()->json($breaking, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(breaking $breaking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(breaking $breaking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $breaking = breaking::find($id);
        if (!$breaking) {
            return response()->json(['error' => 'Breaking not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'sorted' => 'sometimes|nullable',
            'name' => 'sometimes|nullable',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['error' => $errors], 422);
        }
        if ($request->has('sorted')) {
            $breaking->sorted = $request->input('sorted');
        }
        if ($request->has('name')) {
            $breaking->name = $request->input('name');
        }
        $breaking->save();
        return response()->json($breaking);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $breaking = breaking::find($id);
        if (!$breaking) {
            return response()->json(['error' => 'Breaking not found'], 404);
        }
        $breaking->delete();
        return response()->json(['message' => 'Breaking deleted successfully'], 200);
    }

    public function getBreakingsByPathId($pathId)
    {
        $breakings = Breaking::where('path_id', $pathId)
        ->orderBy('sorted', 'asc')
        ->get();
        if ($breakings->isEmpty()) {
            return response()->json(['error' => 'No breakings found for path with ID ' . $pathId], 404);
        }
        return $breakings;

    }
}
