<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VWound extends Model
{
    protected $table = 'vw_wound_details'; // Especifica que este modelo representa la vista
    public $timestamps = false; // Las vistas no tienen timestamps automáticamente
}
