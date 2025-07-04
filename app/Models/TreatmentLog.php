<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentLog extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $primaryKey = 'id_treatmentLog';


    public function treatment()
    {
        return $this->belongsTo(Treatment::class); //, 'id_treatment'
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class); //, 'id_appointment'
    }
}
