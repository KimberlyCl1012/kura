<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoundLog extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $primaryKey = 'id_woundLog';

    public function wound()
    {
        return $this->hasMany(Wound::class);  //, 'id_wound'
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);  //, 'ID_Tratamiento'
    }

    public function measurement()
    {
        return $this->belongsTo(Measurement::class);  //, 'ID_Medicion'
    }

}
