<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
     use HasFactory;
    protected $table = 'list_sites';
    protected $guarded = [];
    // protected $primaryKey = 'id_site';

    public function address()
    {
        return $this->belongsTo(Address::class); //, 'id_address'
    }

    public function patient()
    {
        return $this->hasMany(Patient::class); //, 'id_userDetail'
    }

    public function appointment()
    {
        return $this->hasMany(Appointment::class); //, 'id_appointment'
    }    

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class); //, 'id_appointment'
    }   
}
