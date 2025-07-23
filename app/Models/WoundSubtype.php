<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoundSubtype extends Model
{
    use HasFactory;
    protected $table = 'list_wound_subtypes';
    protected $guarded = [];

    public function type()
    {
        return $this->belongsTo(WoundType::class, 'wound_type_id');
    }
}
