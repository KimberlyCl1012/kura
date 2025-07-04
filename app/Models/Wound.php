<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wound extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $primaryKey = 'id_wound';

    public function healthRecord()
    {
        return $this->belongsTo(HealthRecord::class);  // , 'id_healthRecord'
    }

    public function woundPhase()
    {
        return $this->belongsTo(WoundPhase::class);  // , 'id_woundPhase'
    }

    public function woundType()
    {
        return $this->belongsTo(WoundType::class);  // , 'id_woundType'
    }

    public function bodyLocation()
    {
        return $this->belongsTo(BodyLocation::class);  // , 'id_bodyLocation'
    }
}
