<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'database_table',
        'google_link',
        'google_config',
        'spreadsheet_list',
    ];

    // Cast google_config as array (since it's JSON)
    protected $casts = [
        'google_config' => 'array',
    ];
}
