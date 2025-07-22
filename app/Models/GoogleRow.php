<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleRow extends Model
{
    use HasFactory;

    protected $fillable = ['google_row', 'text', 'status'];
}
