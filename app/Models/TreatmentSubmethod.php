<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentSubmethod extends Model
{
     protected $fillable = [
        'treatment_id',
        'treatment_submethod_id',
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function submethod()
    {
        return $this->belongsTo(ListTreatmentSubmethod::class, 'treatment_submethod_id');
    }
}
