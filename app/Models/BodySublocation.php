<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodySublocation extends Model
{
    use HasFactory;
    protected $table = 'list_body_sublocations';
    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(BodyLocation::class, 'body_location_id');
    }
}
