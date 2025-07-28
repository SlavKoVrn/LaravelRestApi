<?php

namespace App\Http\Controllers;

use App\Models\GoogleLink;
use App\Services\GoogleSheetsService;

use Illuminate\Http\Request;
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

        if (!$tableName){
            $tableName = 'google_links';
        }

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
        $key = strtolower($column->Key);

        // === Check for UNIQUE KEY ===
        $isUnique = ($key === 'uni');

        // Helper: Generate random string
        $randomString = function ($length = 10) {
            return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, $length);
        };

        // Helper: Generate random number string
        $randomNumber = function ($digits = 6) {
            return rand(1, 9) . str_pad(rand(0, 10 ** ($digits - 1) - 1), $digits - 1, '0', STR_PAD_LEFT);
        };

        // === ENUM('value1','value2',...) ===
        if (str_starts_with($type, 'enum')) {
            if (preg_match_all("/'([^']+)'/", $type, $matches)) {
                $enumValues = $matches[1];
                if (!empty($enumValues)) {
                    // If unique, try to randomize choice; else pick first or random
                    return $isUnique ? $enumValues[array_rand($enumValues)] : ($enumValues[0] ?? '');
                }
            }
            return '';
        }

        // === SET(...) ===
        if (str_starts_with($type, 'set')) {
            return ''; // SET not supported in dummy data (multiple values)
        }

        // === INTEGER TYPES ===
        if (preg_match('/^(tinyint|smallint|mediumint|int|integer|bigint)/', $type)) {
            return $isUnique ? time() + rand(1, 100000) : 0;
        }

        // === BOOLEAN / TINYINT(1) ===
        if (preg_match('/^tinyint\s*\(\s*1\s*\)/', $type)) {
            return !$isUnique ? 0 : rand(0, 1); // Still binary, but random if unique
        }

        // === DECIMAL / NUMERIC ===
        if (preg_match('/^(decimal|numeric)/', $type)) {
            return $isUnique ? round(rand(1, 100000) / 100, 2) : 0.0;
        }

        // === FLOAT / DOUBLE ===
        if (str_contains($type, 'float') || str_contains($type, 'double')) {
            return $isUnique ? round(rand(1, 100000) / 100, 6) : 0.0;
        }

        // === CHAR(M) ===
        if (str_starts_with($type, 'char')) {
            if (preg_match('/char\s*\(\s*(\d+)\s*\)/', $type, $matches)) {
                $len = (int)$matches[1];
                return $isUnique ? str_pad($randomString($len), $len, '*', STR_PAD_RIGHT) : '';
            }
            return '';
        }

        // === VARCHAR(M) ===
        if (str_starts_with($type, 'varchar')) {
            if (preg_match('/varchar\s*\(\s*(\d+)\s*\)/', $type, $matches)) {
                $len = (int)$matches[1];
                if ($isUnique) {
                    $prefix = 'uniq_';
                    $availableLen = max(5, $len - strlen($prefix));
                    return $prefix . $randomString($availableLen);
                }
                return '';
            }
            return '';
        }

        // === TEXT TYPES ===
        if (preg_match('/^(tinytext|text|mediumtext|longtext)$/', $type)) {
            return $isUnique ? 'unique_text_' . uniqid() : '';
        }

        // === BINARY / VARBINARY ===
        if (str_starts_with($type, 'binary') || str_starts_with($type, 'varbinary')) {
            return null; // Cannot meaningfully dummy binary; leave null
        }

        // === BLOB TYPES ===
        if (preg_match('/^(tinyblob|blob|mediumblob|longblob)$/', $type)) {
            return null;
        }

        // === DATETIME ===
        if (str_starts_with($type, 'datetime')) {
            return $isUnique ? now()->addSeconds(rand(1, 3600))->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
        }

        // === DATE ===
        if (str_starts_with($type, 'date')) {
            return $isUnique ? now()->addDays(rand(1, 30))->format('Y-m-d') : now()->format('Y-m-d');
        }

        // === TIMESTAMP ===
        if (str_starts_with($type, 'timestamp')) {
            return $isUnique ? now()->addSeconds(rand(1, 3600))->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
        }

        // === TIME ===
        if (str_starts_with($type, 'time')) {
            return $isUnique ? now()->addHours(rand(1, 24))->format('H:i:s') : now()->format('H:i:s');
        }

        // === YEAR ===
        if (str_starts_with($type, 'year')) {
            return $isUnique ? (string)(date('Y') + rand(1, 10)) : date('Y');
        }

        // === JSON ===
        if (str_contains($type, 'json')) {
            return $isUnique ? '{"dummy":"unique_' . uniqid() . '"}' : '{}';
        }

        // === SPATIAL TYPES ===
        if (preg_match('/^(geometry|point|linestring|polygon|multipoint|multipolygon|geometrycollection)$/', $type)) {
            return null;
        }

        // === BIT FIELD ===
        if (str_starts_with($type, 'bit')) {
            return $isUnique ? 1 : 0; // Often used like boolean flags
        }

        // === FALLBACK ===
        return $isUnique ? 'unique_' . uniqid() : '';
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

    /**
     * Export table to Google Sheet
     *
     * @param  string  $tableName
     * @return \Illuminate\Http\Response
     */
    public function exportRows($tableName)
    {
        try {
            // Get all records from the table
            $rows = DB::table($tableName)->get()->toArray();

            if (empty($rows)) {
                return redirect()->route('google-tables')->with('error', 'No data to export.');
            }

            // Get column names from the table schema
            $columns = Schema::getColumnListing($tableName);
            $data = [$columns]; // First row is header

            // Convert each row object to array and map values in correct column order
            foreach ($rows as $row) {
                $dataRow = [];
                foreach ($columns as $column) {
                    $dataRow[] = $row->$column ?? '';
                }
                $data[] = $dataRow;
            }

            // Use Google Sheets Service to write data
            $googleLink = GoogleLink::all()->where('database_table', $tableName)->first();
            $credentials = json_decode($googleLink->google_config, true);
            $spreadsheetId = '';
            if (preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $googleLink->google_link, $matches)) {
                $spreadsheetId = $matches[1];
            }
            if (empty($spreadsheetId)){
                return redirect()->route('google-tables')->with('error', 'Spread Sheet Id NOT DEFINED ');
            }
            $googleSheetsService = new GoogleSheetsService($credentials, $spreadsheetId);

            // Define sheet range (e.g., 'Sheet1!A1')
            $sheetName = $googleLink->spreadsheet_list;
            $range = "{$sheetName}!A1";

            $googleSheetsService->writeSheet($range, $data);

            return redirect()->route('google-tables')
                ->with('success', "Data from '{$tableName}' exported to Google Sheets successfully!");

        } catch (\Exception $e) {
            return redirect()->route('google-tables')->with('error', 'Failed to export data: ' . $e->getMessage());
        }
    }

    /**
     * Import table to Google Sheet
     *
     * @param  string  $tableName
     * @return \Illuminate\Http\Response
     */
    public function importRows($tableName)
    {
        try {
            // Initialize Google Sheets Service
            $googleLink = GoogleLink::all()->where('database_table', $tableName)->first();
            $credentials = json_decode($googleLink->google_config, true);
            $spreadsheetId = '';
            if (preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $googleLink->google_link, $matches)) {
                $spreadsheetId = $matches[1];
            }
            if (empty($spreadsheetId)){
                return redirect()->route('google-tables')->with('error', 'Spread Sheet Id NOT DEFINED ');
            }
            $googleSheets = new GoogleSheetsService($credentials, $spreadsheetId);

            // Define sheet range (adjust as needed)
            $sheetName = $googleLink->spreadsheet_list;
            $range = $googleSheets->getUsedRange($sheetName);

            $values = $googleSheets->readSheet($range);

            if (empty($values)) {
                return redirect()->route('google-tables')
                    ->with('error', 'No data found in the sheet.');
            }

            // First row = headers
            $headers = $values[0];
            $headers = array_map('trim', $headers);
            $headers = array_map([Str::class, 'snake'], $headers); // Normalize to snake_case

            // Get actual DB columns
            $dbColumns = Schema::getColumnListing($tableName);
            $validColumns = array_intersect($dbColumns, $headers);

            if (empty($validColumns)) {
                return redirect()->route('google-tables')
                    ->with('error', 'No matching columns found between sheet and table.');
            }

            $importedCount = 0;
            $rowsToInsert = [];

            // Loop through data rows (skip header)
            foreach (array_slice($values, 1) as $row) {
                $rowData = [];

                // Map each header to its value
                foreach ($headers as $index => $header) {
                    if (in_array($header, $dbColumns)) {
                        $value = $index < count($row) ? trim($row[$index]) : null;
                        $rowData[$header] = $value === '' ? null : $value;
                    }
                }

                if (count($rowData) > 0) {
                    $rowsToInsert[] = $rowData;
                    $importedCount++;
                }
            }

            if ($importedCount > 0) {
                // Insert in chunks to avoid memory issues
                collect($rowsToInsert)->chunk(100)->each(function ($chunk) use ($tableName) {
                    DB::table($tableName)->insertOrIgnore($chunk->toArray());
                });

                return redirect()->route('google-tables')
                    ->with('success', "$importedCount row(s) imported into '$tableName' from Google Sheets.");
            }

            return redirect()->route('google-tables')->with('success', 'No rows to import.');

        } catch (\Exception $e) {
            return redirect()->route('google-tables')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Export table to Google Sheet via AJAX with progress updates
     *
     * @param string $tableName
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportRowsAjax($tableName)
    {
        // === Only start/clean buffers if not already flushed ===
        if (ob_get_level() == 0) {
            ob_start(); // Start one buffer if none exists
        } else {
            // Clean all existing buffers without flushing to browser yet
            while (ob_get_level()) {
                ob_end_clean();
            }
            ob_start();
        }

        // Disable compression that could break chunked transfer
        if (extension_loaded('zlib') && ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        try {
            $rows = DB::table($tableName)->get()->toArray();
            if (empty($rows)) {
                echo json_encode([
                        'success' => false,
                        'message' => 'No data to export.',
                        'progress' => 0,
                    ]) . "\n";
                ob_flush();
                flush();
                return response('', 200)->header('Content-Type', 'application/x-ndjson');
            }

            $total = count($rows);
            $columns = Schema::getColumnListing($tableName);
            $data = [$columns]; // Header row

            $chunkSize = 100;
            $processed = 0;

            foreach (array_chunk($rows, $chunkSize) as $chunk) {
                foreach ($chunk as $row) {
                    $dataRow = [];
                    foreach ($columns as $column) {
                        $dataRow[] = $row->$column ?? '';
                    }
                    $data[] = $dataRow;
                }
                $processed += count($chunk);

                // Send progress update
                echo json_encode([
                        'progress' => round(($processed / $total) * 100),
                        'processed' => $processed,
                        'total' => $total,
                        'status' => 'Exporting... please wait.'
                    ]) . "\n";

                // Flush to client
                ob_flush();
                flush();
            }

            // === Perform Google Sheets Export ===
            $googleLink = GoogleLink::where('database_table', $tableName)->first();
            if (!$googleLink) {
                throw new \Exception("Google link not configured for table {$tableName}.");
            }

            $credentials = json_decode($googleLink->google_config, true);
            $spreadsheetId = '';
            if (!preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $googleLink->google_link, $matches)) {
                throw new \Exception('Spreadsheet ID not found in Google link.');
            }
            $spreadsheetId = $matches[1];
            $sheetName = $googleLink->spreadsheet_list;
            $range = "{$sheetName}!A1";

            $googleSheetsService = new GoogleSheetsService($credentials, $spreadsheetId);
            $googleSheetsService->writeSheet($range, $data);

            // Final success message
            echo json_encode([
                    'success' => true,
                    'progress' => 100,
                    'status' => 'Export completed!',
                    'message' => "Successfully exported {$total} rows to Google Sheets."
                ]) . "\n";

        } catch (\Exception $e) {
            echo json_encode([
                    'success' => false,
                    'progress' => 0,
                    'status' => 'Error occurred',
                    'message' => 'Export failed: ' . $e->getMessage()
                ]) . "\n";
        }

        // === Only end the buffer if one exists ===
        if (ob_get_level() > 0) {
            ob_end_flush(); // Ends the current buffer and flushes
        } else {
            // If no buffer, just flush any pending output
            flush();
        }

        // Ensure response headers are set
        return response('', 200)->header('Content-Type', 'application/x-ndjson');
    }

}
