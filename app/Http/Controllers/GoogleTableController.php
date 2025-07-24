<?php

namespace App\Http\Controllers;

use App\Models\GoogleLink;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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

        return view('google-tables.index', compact('googleLinks','googleLink', 'search', 'data', 'columns', 'tableName'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $tableName
     * @return \Illuminate\Http\Response
     */
    public function create($tableName)
    {
        // Optional: Whitelist allowed tables (security!)
        /*
        $allowedTables = ['users', 'posts', 'categories'];
        if (!in_array($tableName, $allowedTables)) {
            abort(404);
        }
        */

        if (!Schema::hasTable($tableName)) {
            abort(404);
        }

        // Get column info (for form fields)
        $columns = DB::select("DESCRIBE `$tableName`");
        // Exclude primary key if it's auto-increment
        $createColumns = collect($columns)->reject(function ($col) {
            return $col->Key == 'PRI' && strpos($col->Extra, 'auto_increment') !== false;
        });

        return view('google-tables.create', compact('tableName', 'createColumns'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $tableName
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $tableName)
    {
        /*
        $allowedTables = ['users', 'posts', 'categories']; // Update as needed
        if (!in_array($tableName, $allowedTables)) {
            abort(404);
        }
        */

        if (!Schema::hasTable($tableName)) {
            abort(404);
        }

        // Get column types to validate
        $columns = DB::select("DESCRIBE `$tableName`");
        $insertableColumns = collect($columns)
            // Exclude auto-increment primary key
            ->reject(fn($col) => $col->Key == 'PRI' && strpos($col->Extra, 'auto_increment') !== false)
            ->pluck('Field')
            ->toArray();

        // Build validation rules
        $rules = [];
        foreach ($insertableColumns as $field) {
            $rules[$field] = 'nullable|string'; // Adjust if you have numbers, emails, etc.
        }

        $validated = $request->validate($rules);

        // Insert the new record
        try {
            DB::table($tableName)->insert($validated);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('google-tables.index', $tableName)
            ->with('success', 'Record created successfully!');
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
     * @param  string  $tableName
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($tableName, $id)
    {
        // Optional: Whitelist allowed tables (security!)
        /*
        $allowedTables = ['users', 'posts', 'categories'];
        if (!in_array($tableName, $allowedTables)) {
            abort(404);
        }
        */

        // Fetch the record
        $record = DB::table($tableName)->where('id', $id)->first();

        if (!$record) {
            abort(404);
        }

        // Get column info (for form fields)
        $columns = DB::select("DESCRIBE `$tableName`");

        // Pass data to view
        return view('google-tables.edit', compact('tableName', 'id', 'record', 'columns'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $tableName
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $tableName, $id)
    {
        /*
        $allowedTables = ['users', 'posts', 'categories']; // Update as needed
        if (!in_array($tableName, $allowedTables)) {
            abort(404);
        }
        */

        // Get column types to validate
        $columns = DB::select("DESCRIBE `$tableName`");
        $updatableColumns = collect($columns)
            ->reject(fn($col) => $col->Key == 'PRI') // Exclude primary key
            ->pluck('Field')
            ->toArray();

        // Build validation rules
        $rules = [];
        foreach ($updatableColumns as $field) {
            $rules[$field] = 'nullable|string'; // Adjust if you have numbers, emails, etc.
        }

        $validated = $request->validate($rules);

        // Update the record
        try {
            DB::table($tableName)->where('id', $id)->update($validated);
        }catch (\Exception $e){
            return redirect()->back()->withErrors(['error' =>$e->getMessage()])->withInput();
        }

        return redirect()
            ->route('google-tables.index', $tableName)
            ->with('success', 'Record updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $tableName
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($tableName, $id)
    {
        try {
            DB::table($tableName)->where('id', $id)->delete();
        }catch (\Exception $e){
            return redirect()->back()->withErrors(['error' =>$e->getMessage()]);
        }
        return redirect()->back()->with('success', 'Record deleted.');
    }

    /**
     * Generate records for table
     *
     * @param  string  $tableName
     * @return \Illuminate\Http\Response
     */
    public function generateRows($tableName)
    {
        $count = 1000;
        $batchSize = 500;

        // Get the list of columns and filter out the primary key
        $columns = DB::select("DESCRIBE `$tableName`");
        $updatableColumns = collect($columns)
            ->reject(fn($col) => $col->Key == 'PRI') // Exclude primary key
            ->toArray();

        if (empty($updatableColumns)) {
            return redirect()
                ->route('google-tables')
                ->with('error', "No updatable columns found in table '{$tableName}'.");
        }

        try {
            DB::transaction(function () use ($count, $batchSize, $tableName, $updatableColumns) {
                $data = [];

                for ($i = 1; $i <= $count; $i++) {
                    $row = [];
                    foreach ($updatableColumns as $column) {
                        $row[$column->Field] = $this->generateDummyValue($column);
                    }
                    $data[] = $row;

                    // Insert in batches
                    if ($i % $batchSize === 0 || $i === $count) {
                        DB::table($tableName)->insert($data);
                        $data = []; // Reset batch
                    }
                }
            });
        }catch (\Exception $e){
            return redirect()->back()->withErrors(['error' =>$e->getMessage()]);
        }

        return redirect()
            ->route('google-tables')
            ->with('success', "Successfully generated {$count} {$tableName} rows!");
    }

    private function generateDummyValue($column)
    {
        $type = strtolower($column->Type);
        $field = strtolower($column->Field);

        if (str_starts_with($type, 'enum')) {
            // Match values inside ENUM: 'val1','val2','val3'
            if (preg_match_all("/'([^']+)'/", $column->Type, $matches)) {
                $enumValues = $matches[1]; // These are the actual enum options
                return $enumValues[array_rand($enumValues)]; // Pick random
            }
            return 'Unknown'; // Fallback if parsing fails
        }

        if (str_contains($type, 'varchar') || str_starts_with($type, 'char')) {
            $length = 255; // default
            if (preg_match('/\((\d+)\)/', $type, $matches)) {
                $length = (int)$matches[1];
            }
            return Str::random(min($length, 20)); // reasonable size
        }

        if (str_contains($type, 'text')) {
            return 'Lorem ipsum dolor sit amet...'; // or use Faker
        }

        if (str_contains($type, 'int') || str_contains($type, 'tinyint') || str_contains($type, 'bigint')) {
            return rand(1, 1000);
        }

        if (str_contains($type, 'decimal') || str_contains($type, 'double') || str_contains($type, 'float')) {
            return round(rand(100, 999) / 10, 2);
        }

        if (str_contains($type, 'timestamp') || str_contains($type, 'datetime') || str_contains($field, 'created_at') || str_contains($field, 'updated_at')) {
            return now()->subDays(rand(0, 365))->format('Y-m-d H:i:s');
        }

        if (str_contains($type, 'date')) {
            return now()->subDays(rand(0, 365))->format('Y-m-d');
        }

        if (str_contains($type, 'tinyint(1)') || str_contains($field, 'is_')) {
            return rand(0, 1); // boolean
        }

        // Fallback
        return 'Generated value';
    }

    /**
     * Truncate table
     *
     * @param  string  $tableName
     * @return \Illuminate\Http\Response
     */
    public function removeRows($tableName)
    {
        try {
            DB::table($tableName)->truncate();
        }catch (\Exception $e){
            return redirect()->back()->withErrors(['error' =>$e->getMessage()]);
        }
        return redirect()
            ->route('google-tables')
            ->with('success', "Rows truncated!");
    }

}
