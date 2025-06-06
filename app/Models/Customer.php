<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{   
    protected $fillable = [
        'name',
        'phone_number',
        'address',
    ];

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
}
