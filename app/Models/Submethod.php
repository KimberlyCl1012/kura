<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submethod extends Model
{
    use HasFactory;
    protected $table = 'list_treatment_submethods';
    protected $guarded = [];

    public function treatmentMethod()
    {
        return $this->belongsTo(Method::class);
    }
}
