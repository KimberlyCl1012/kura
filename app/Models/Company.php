<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $table = 'list_companies';
    protected $guarded = [];
    // protected $primaryKey = 'id_company';

    public function usersDetail()
    {
        return $this->hasMany(UserDetail::class); // , 'id_company'
    }
}
