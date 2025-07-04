<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodyLocation extends Model
{
    use HasFactory;
    protected $table = 'list_body_locations';
    protected $guarded = [];
    // protected $primaryKey = 'id_bodyLocation';

    public function wounds()
    {
        return $this->hasMany(Wound::class); // , 'id_bodyLocation'
    }
}
