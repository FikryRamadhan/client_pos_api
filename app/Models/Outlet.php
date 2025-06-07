<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'capacity',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function stock(){
        return $this->hasMany(Stock::class, 'outlet_id');
    }
}
