<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $table = 'list_addresses';
    // protected $fillable = ['Tipo', 'Calle'];
    protected $guarded = [];
    // protected $primaryKey = 'id_address';

    public function sites()
    {
        return $this->hasMany(Site::class); //, 'id_site'
    }
}
