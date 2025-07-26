<?php
namespace App\Http\Controllers;

use App\Models\GoogleLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GoogleLinkController extends Controller
{
    public function index()
    {
        $googleLinks = GoogleLink::all();
        return view('google-links.index', compact('googleLinks'));
    }

    public function create()
    {
        $tables = $this->getDatabaseTables();
        return view('google-links.create', compact('tables'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'database_table' => [
                'required',
                'string',
                'in:' . implode(',', $this->getDatabaseTables()),
                'unique:google_links,database_table',
            ],
            'google_link' => 'required|url|max:255',
            'spreadsheet_list' => 'required|max:255',
            'google_config' => 'nullable|file|mimes:json',
        ], [
            'database_table.required' => 'The database table is required.',
            'database_table.in' => 'The selected database table is invalid.',
            'database_table.unique' => 'This table has already been configured.',
            'google_link.url' => 'Please enter a valid URL.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $configJson = '{}';
        if ($request->hasFile('google_config')) {
            $file = $request->file('google_config');

            // Validate JSON
            $jsonContent = file_get_contents($file->getRealPath());
            json_decode($jsonContent);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['google_config' => 'Invalid JSON file.'])->withInput();
            }

            // Store the file and save the path
            $configJson = $file->store('google-config');
        }

        try {
            GoogleLink::create([
                'database_table' => $request->database_table,
                'google_link'    => $request->google_link,
                'google_config'  => $configJson, // Stored as JSON string
            ]);
        }catch (\Exception $e){
            return redirect()->back()->withErrors(['error' =>$e->getMessage()]);
        }

        return redirect()->route('google-links.index')
            ->with('success', 'Google link created successfully.');
    }

    public function edit(GoogleLink $googleLink)
    {
        $tables = $this->getDatabaseTables();
        return view('google-links.edit', compact('googleLink', 'tables'));
    }

    public function update(Request $request, GoogleLink $googleLink)
    {
        $validator = Validator::make($request->all(), [
            'database_table' => [
                'required',
                'string',
                'in:' . implode(',', $this->getDatabaseTables()),
                Rule::unique('google_links', 'database_table')->ignore($googleLink->id),
            ],
            'google_link' => 'required|url|max:255',
            'spreadsheet_list' => 'required|max:255',
            'google_config' => 'nullable|file|mimes:json',
        ], [
            'database_table.required' => 'The database table is required.',
            'database_table.in' => 'The selected database table is invalid.',
            'database_table.unique' => 'This table has already been configured.',
            'google_link.url' => 'Please enter a valid URL.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['database_table', 'google_link']);

        // Only update config if new file is uploaded
        if ($request->hasFile('google_config')) {
            $configJson = file_get_contents($request->file('google_config')->getRealPath());
            json_decode($configJson);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->withErrors(['google_config' => 'Invalid JSON file.'])->withInput();
            }
            $data['google_config'] = $configJson;
        }

        try {
            $googleLink->update($data);
        }catch (\Exception $e){
            return redirect()->back()->withErrors(['error' =>$e->getMessage()]);
        }

        return redirect()->route('google-links.index')
            ->with('success', 'Google link updated successfully.');
    }

    public function destroy(GoogleLink $googleLink)
    {
        try {
            $googleLink->delete();
        }catch (\Exception $e){
            return redirect()->back()->withErrors(['error' =>$e->getMessage()]);
        }
        return redirect()->route('google-links.index')
            ->with('success', 'Google link deleted.');
    }

    // Helper: Get all table names
    private function getDatabaseTables()
    {
        // Use Laravel's DB facade to run raw SQL
        $tables = \DB::select('SHOW TABLES');

        // The result column depends on your database name
        $databaseName = config('database.connections.' . config('database.default') . '.database');

        $columnName = 'Tables_in_' . $databaseName;

        return array_column($tables, $columnName);
    }

}
