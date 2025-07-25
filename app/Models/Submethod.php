<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submethod extends Model
{
     use HasFactory;

    protected $table = 'list_treatment_submethods';

    public function method()
    {
        return $this->belongsTo(ListTreatmentMethod::class, 'treatment_method_id');
    }
}
