<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Method extends Model
{
    use HasFactory;

    protected $table = 'list_treatment_methods';

    public function submethods()
    {
        return $this->hasMany(Submethod::class, 'treatment_method_id');
    }
}
