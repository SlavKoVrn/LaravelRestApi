<?php

namespace App\Http\Controllers;

use App\Models\GoogleRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoogleRowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $googleRows = GoogleRow::orderBy('created_at', 'desc')->paginate(10);
        return view('google-rows.index', compact('googleRows'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('google-rows.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'google_row' => 'required|string|max:10|unique:google_rows',
            'text'       => 'required|string|max:255',
            'status'     => 'required|in:Allowed,Prohibited',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        GoogleRow::create($request->only(['google_row', 'text', 'status']));

        return redirect()->route('google-rows.index')
            ->with('success', 'Row created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GoogleRow $googleRow)
    {
        return view('google-rows.show', compact('googleRow'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GoogleRow $googleRow)
    {
        return view('google-rows.edit', compact('googleRow'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GoogleRow $googleRow)
    {
        $validator = Validator::make($request->all(), [
            'google_row' => 'required|string|max:10|unique:google_rows,google_row,' . $googleRow->id,
            'text'       => 'required|string|max:255',
            'status'     => 'required|in:Allowed,Prohibited',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $googleRow->update($request->only(['google_row', 'text', 'status']));

        return redirect()->route('google-rows.index')
            ->with('success', 'Row updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GoogleRow $googleRow)
    {
        $googleRow->delete();

        return redirect()->route('google-rows.index')
            ->with('success', 'Row deleted successfully.');
    }
}