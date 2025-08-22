<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    protected $table = 'permissions';
    protected $guarded = [];
    protected $fillable = ['name', 'slug', 'description', 'state'];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_permissions')
            ->withTimestamps();
    }
}
