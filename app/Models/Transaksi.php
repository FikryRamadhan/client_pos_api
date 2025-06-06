<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $guarded = [];

    public function kasir(){
        return $this->belongsTo(User::class, 'id_kasir');
    }

    public function pelanggan(){
        return $this->belongsTo(Pelanggan::class, 'id_pelanggans');
    }
}
