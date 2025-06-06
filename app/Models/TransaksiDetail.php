<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    protected $guarded = [];

    public function product(){
        return $this->belongsTo(Product::class, 'id_product');
    }
}
