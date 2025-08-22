<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoundAssessment extends Model
{
     use HasFactory;
    protected $table = 'list_wound_assessments';
     protected $fillable = [
        'type',
        'name',
        'description',
        'state',
    ];

    public function scopeType($q, string $type) {
        return $q->where('type', $type);
    }
}
