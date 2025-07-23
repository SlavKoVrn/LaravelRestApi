<?php

namespace App\Http\Controllers;

use App\Models\GoogleLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class GoogleTableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('search')) {
            $request->validate(['search' => 'nullable|string|max:255']);
            $search = $request->search;
            Session::put('search', $search);
        } else {
            $search = Session::get('search');
        }

        $tableName = null;
        if ($request->query('database_table')) {
            $request->validate([
                'database_table' => 'required|string',
            ]);
            Session::put('table_name', $request->input('database_table'));
        }
        $tableName = Session::get('table_name');

        $data = null;
        $columns = [];
        if (Schema::hasTable($tableName)) {
            $columns = DB::select("DESCRIBE `$tableName`");

            $unsearchableTypes = ['blob', 'binary', 'varbinary', 'geometry'];
            $allColumnNames = collect($columns)
                ->filter(function ($column) use ($unsearchableTypes) {
                    $type = strtolower(explode('(', $column->Type)[0]);
                    return !in_array($type, $unsearchableTypes);
                })
                ->pluck('Field');

            $query = DB::table($tableName);
            if ($search && $allColumnNames->isNotEmpty()) {
                $query->where(function ($inner) use ($allColumnNames, $search) {
                    foreach ($allColumnNames as $column) {
                        // Use CAST to convert any type (int, date, etc.) to string
                        $inner->orWhereRaw('CAST(`' . $column . '` AS CHAR) LIKE ?', ['%' . $search . '%']);
                    }
                });
            }
            $data = $query->paginate(20);
        }

        $googleLinks = GoogleLink::all();
        $googleLink = $googleLinks->where('database_table', $tableName)->first();

        return view('google-tables.index', compact('googleLinks','googleLink', 'search', 'data', 'columns'));
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
