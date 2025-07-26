<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_links', function (Blueprint $table) {
            $table->string('spreadsheet_list')->default('Лист1')->after('google_config');
        });

        // Optionally, if you want to explicitly set value for existing rows
        // Laravel will auto-fill default for new rows, but existing ones get default too in most cases.
        // However, if you want to be safe:
        \DB::statement("UPDATE google_links SET spreadsheet_list = 'Лист1' WHERE spreadsheet_list IS NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('google_links', function (Blueprint $table) {
            $table->dropColumn('spreadsheet_list');
        });
    }
};