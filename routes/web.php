<?php

use App\Http\Controllers\GoogleRowController;
use App\Http\Controllers\GoogleLinkController;
use App\Http\Controllers\GoogleTableController;
use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/table-view', function () {
    return view('table-view');
});

Route::post('/get-table-data', function (Request $request) {
    $tableName = $request->input('table_name');

    // Validate table name (optional: whitelist allowed tables)
    $allowedTables = DB::select('SHOW TABLES');
    $tableNames = [];
    $key = 'Tables_in_' . env('DB_DATABASE'); // MySQL specific
    foreach ($allowedTables as $table) {
        $tableNames[] = $table->$key;
    }

    if (!in_array($tableName, $tableNames)) {
        return response()->json(['error' => 'Invalid table name.'], 400);
    }

    try {
        $columns = Schema::getColumnListing($tableName);
        $rows = DB::table($tableName)->get();

        return response()->json([
            'columns' => $columns,
            'rows' => $rows
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to fetch data.'], 500);
    }
});

Route::resource('google-links', GoogleLinkController::class);
//Route::resource('google-tables', GoogleTableController::class);
Route::get('/google-tables', [GoogleTableController::class, 'index'])
    ->name('google-tables');


Route::get('/', function () {
    return view('welcome');
});

Route::get('/google-rows/generate', [GoogleRowController::class, 'generateRows'])
    ->name('google-rows.generate');

Route::get('/google-rows/remove', [GoogleRowController::class, 'removeRows'])
    ->name('google-rows.remove');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('google-rows', GoogleRowController::class);

