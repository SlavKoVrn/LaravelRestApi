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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/google-rows/generate', [GoogleRowController::class, 'generateRows'])->name('google-rows.generate');
Route::get('/google-rows/remove', [GoogleRowController::class, 'removeRows'])->name('google-rows.remove');
Route::resource('google-links', GoogleLinkController::class);
Route::resource('google-rows', GoogleRowController::class);

Route::get('/google-tables/export-init/{tableName}', [
    App\Http\Controllers\GoogleTableController::class, 'exportInitAjax'
])->name('google-tables.export-init');
Route::post('/google-tables/export-chunk/{tableName}', [
    App\Http\Controllers\GoogleTableController::class, 'exportChunkAjax'
])->name('google-tables.export-chunk');

Route::get('/google-tables/import-init/{tableName}', [
    App\Http\Controllers\GoogleTableController::class, 'importInitAjax'
])->name('google-tables.import-init');
Route::post('/google-tables/import-chunk/{tableName}', [
    App\Http\Controllers\GoogleTableController::class, 'importChunkAjax'
])->name('google-tables.import-chunk');


Route::get('/google-tables', [GoogleTableController::class, 'index'])->name('google-tables');
Route::put('/google-tables/{tableName}/{id}', [GoogleTableController::class, 'update'])->name('google-tables.update');
Route::get('/google-tables/create/{tableName}', [GoogleTableController::class, 'create'])->name('google-tables.create');
Route::post('/google-tables/store/{tableName}', [GoogleTableController::class, 'store'])->name('google-tables.store');
Route::prefix('table')->group(function () {
    Route::get('/{table}', [GoogleTableController::class, 'index'])->name('google-tables.index');
    Route::get('/{table}/edit/{id}', [GoogleTableController::class, 'edit'])->name('google-tables.edit');
    Route::delete('/{table}/{id}', [GoogleTableController::class, 'destroy'])->name('google-tables.destroy');
    Route::get('/{table}/truncate', [GoogleTableController::class, 'removeRows'])->name('google-tables.truncate');
    Route::get('/{table}/generate', [GoogleTableController::class, 'generateRows'])->name('google-tables.generate');
    Route::get('/{table}/export', [GoogleTableController::class, 'exportRows'])->name('google-tables.export');
    Route::get('/{table}/import', [GoogleTableController::class, 'importRows'])->name('google-tables.import');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

