<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function stock(){
        return $this->hasMany(Stock::class, 'id_product');
    }

    public function TransaksiDetail(){
        return $this->hasMany(TransaksiDetail::class, 'id_products');
    }
}
