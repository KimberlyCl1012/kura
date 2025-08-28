<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurator extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
        'user_uuid',
        'user_detail_id',
        'specialty',
        'detail_specialty',
        'type_kurator',
        'type_identification',
        'identification',
        'state',
        'created_by',
    ];

    public function patient()
    {
        return $this->belongsToMany(Patient::class, 'kurator_patients', 'id_kurator', 'id_patient');
    }

    public function userDetail()
    {
        return $this->belongsTo(UserDetail::class); //, 'id_userDetail'
    }
}
