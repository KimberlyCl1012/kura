<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodyLocation extends Model
{
    use HasFactory;
    protected $table = 'list_body_locations';
    protected $guarded = [];

    public function bodySublocations()
    {
        return $this->hasMany(BodySublocation::class, 'body_location_id');
    }
}
