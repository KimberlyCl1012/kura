<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessChangeLog extends Model
{
    use HasFactory;
    protected $table = 'access_change_logs';
    protected $guarded = [];

    protected $fillable = [
        'user_id',
        'logType',
        'table',
        'primaryKey',
        'secondaryKey',
        'changeType',
        'fieldName',
        'oldValue',
        'newValue',
    ];
}
