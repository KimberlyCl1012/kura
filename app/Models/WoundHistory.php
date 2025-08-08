<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoundHistory extends Model
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

    public function wound()
    {
        return $this->belongsTo(Wound::class);
    }

    public function mediaHistories()
    {
        return $this->hasMany(MediaHistory::class, 'wound_history_id');
    }

    protected $casts = [
        'piel_perilesional' => 'array',
        'infeccion' => 'array',
    ];
}
