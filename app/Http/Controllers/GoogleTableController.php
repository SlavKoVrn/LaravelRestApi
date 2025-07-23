<?php

namespace App\Http\Controllers;

use App\Models\GoogleLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class GoogleTableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Fetch all available "tables" (from GoogleLink or any source)
        $googleLinks = GoogleLink::all();

        $googleLink = null;
        $data = null;
        $columns = [];

        if ($request->query('database_table')) {
            $request->validate([
                'database_table' => 'required|string',
            ]);

            $tableName = $request->input('database_table');

            // Check if table exists in the database
            if (Schema::hasTable($tableName)) {
                // Get first few rows with pagination
                $data = DB::table($tableName)->paginate(20);

                // Get column names
                $tableName = $request->input('database_table');

                $columns = DB::select("DESCRIBE `$tableName`");
                // or
                // $columns = DB::select("SHOW COLUMNS FROM `$tableName`");
            } else {
                return back()->withErrors("Table '$tableName' does not exist.");
            }

            // Retain selected link if needed
            $googleLink = $googleLinks->where('database_table', $tableName)->first();
        }

        return view('google-tables.index', compact('googleLinks','googleLink', 'data', 'columns'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
