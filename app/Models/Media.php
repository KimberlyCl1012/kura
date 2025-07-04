<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $primaryKey = 'id_media';

    public function measurement()
    {
        return $this->belongsTo(Measurement::class); // , 'ID_Medicion'
    }
}
