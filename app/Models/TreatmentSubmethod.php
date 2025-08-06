<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentSubmethod extends Model
{
    protected $fillable = [
        'treatment_id',
        'treatment_method_id',
        'treatment_submethod_id',
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function submethod()
    {
        return $this->belongsTo(Submethod::class, 'treatment_submethod_id');
    }

    public function method()
    {
        return $this->belongsTo(Method::class, 'treatment_method_id');
    }
}
