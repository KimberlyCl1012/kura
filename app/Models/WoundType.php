<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoundType extends Model
{
    use HasFactory;
    protected $table = 'list_wound_types';
    protected $guarded = [];
    // protected $primaryKey = 'id_woundType';

    public function woundSubtypes()
    {
       return $this->hasMany(WoundSubtype::class, 'wound_type_id');
    }
}
