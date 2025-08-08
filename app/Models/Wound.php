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

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }


    public function woundPhase()
    {
        return $this->belongsTo(WoundPhase::class);  // , 'id_woundPhase'
    }

    public function woundType()
    {
        return $this->belongsTo(WoundType::class);  // , 'id_woundType'
    }

    public function woundSubtype()
    {
        return $this->belongsTo(WoundSubtype::class);
    }

    public function bodyLocation()
    {
        return $this->belongsTo(BodyLocation::class);  // , 'id_bodyLocation'
    }

    public function bodySublocation()
    {
        return $this->belongsTo(BodySublocation::class);
    }

    public function histories()
    {
        return $this->hasMany(WoundHistory::class);
    }

    public function followUps()
    {
        return $this->hasMany(Appointment::class, 'wound_id');
    }
    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    protected $casts = [
        'piel_perilesional' => 'array',
        'infeccion' => 'array',
    ];
}
