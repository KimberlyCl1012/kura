<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoundPhase extends Model
{
    use HasFactory;
    protected $table = 'list_wound_phases';
    protected $guarded = [];
    // protected $primaryKey = 'id_userDetail';

    public function wound()
    {
        return $this->hasMany(Wound::class);  //, 'id_wound'
    }

    public function currentWoundLog()
    {
        return $this->hasMany(WoundLog::class);  //, 'id_woundPhaseNew'
    }

    public function oldWoundLog()
    {
        return $this->hasMany(WoundLog::class);  //, 'id_woundPhaseOld'
    }
}
