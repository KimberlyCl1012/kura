<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
     use HasFactory;
    protected $guarded = [];
    // protected $primaryKey = 'id_patient';

    public function kurators()
    {
        return $this->belongsToMany(Kurator::class, 'kurator_pacient', 'id_paciente', 'id_kurador');
    }

    public function userDetail()
    {
        return $this->belongsTo(UserDetail::class); //, 'id_userDetail'
    }

    public function site()
    {
        return $this->hasOne(Site::class); //, 'id_site'
    }

}
