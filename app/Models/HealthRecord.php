<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $primaryKey = 'id_healthRecord';

    public function patient()
    {
        return $this->belongsTo(Patient::class); //, 'id_userDetail'
    }

    public function healthInstitution()
    {
        return $this->belongsTo(HealthInstitution::class);
    }
}
