<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'product_id',
        'outlet_id',
        'type',
        'quantity',
        'note',
    ];

    protected static function booted()
    {
        static::created(function ($stock) {
            self::updateProductStock($stock->product_id);
        });

        static::updated(function ($stock) {
            self::updateProductStock($stock->product_id);
        });

        static::deleted(function ($stock) {
            self::updateProductStock($stock->product_id);
        });
    }

    protected static function updateProductStock($productId)
    {
        $stockIn = self::where('product_id', $productId)->where('type', 'in')->sum('quantity');
        $stockOut = self::where('product_id', $productId)->where('type', 'out')->sum('quantity');

        $product = Product::find($productId);
        if ($product) {
            $product->stock = $stockIn - $stockOut;
            $product->save();
        }
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
