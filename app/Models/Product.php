<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'stock',
        'description',
    ];

    public function stocks(){
        return $this->hasMany(Stock::class);
    }

    public function detailTransactions(){
        return $this->hasMany(TransactionDetail::class);
    }
}
