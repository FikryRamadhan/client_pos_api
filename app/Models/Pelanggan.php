<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    public function transaksi(){
        return $this->belongsTo(Transaksi::class, 'id_pelanggan');
    }
}
