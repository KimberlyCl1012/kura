<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurator extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $primaryKey = 'id_kurator';

    public function patient()
    {
        return $this->belongsToMany(Patient::class, 'kurator_pacient', 'id_kurator', 'id_pacient');
    }

    public function userDetail()
    {
        return $this->belongsTo(UserDetail::class); //, 'id_userDetail'
    }
}
