<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $primaryKey = 'id_measurement';

    public function wound()
    {
        return $this->belongsTo(Wound::class);  // , 'id_wound'
    }

    public function treatmentLog()
    {
        return $this->belongsTo(TreatmentLog::class);  // , 'id_wound'
    }

    public function media()
    {
        return $this->hasMany(Media::class); // , 'id_media'
    }
}
