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

    public function wound()
    {
        return $this->hasMany(Wound::class);  //, 'id_wound'
    }
}
