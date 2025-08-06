<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentMethod extends Model
{
      protected $fillable = [
        'treatment_id',
        'treatment_method_id',
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function method()
    {
        return $this->belongsTo(Method::class, 'treatment_method_id');
    }
}
