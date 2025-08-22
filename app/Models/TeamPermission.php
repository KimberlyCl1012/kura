<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPermission extends Model
{
    use HasFactory;
    protected $table = 'team_permissions';
    protected $fillable = ['team_id', 'permission_id'];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
