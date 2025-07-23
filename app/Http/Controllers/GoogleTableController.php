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
        $search = null;
        if ($request->query('search')) {
            $request->validate([
                'search' => 'nullable|string',
            ]);
            Session::put('search', $request->input('search'));
        }
        $search = Session::get('search');

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
            // or
            // $columns = DB::select("SHOW COLUMNS FROM `$tableName`");

            // Extract and filter searchable columns
            $searchableTypes = ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext'];
            $searchableColumns = collect($columns)
                ->filter(function ($column) use ($searchableTypes) {
                    $type = strtolower($column->Type);
                    return in_array(explode('(', $type)[0], $searchableTypes);
                })
                ->pluck('Field');

            $query = DB::table($tableName);

            if ($search && $searchableColumns->isNotEmpty()) {
                $query->where(function ($inner) use ($searchableColumns, $search) {
                    foreach ($searchableColumns as $column) {
                        $inner->orWhere($column, 'LIKE', '%' . $search . '%');
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
