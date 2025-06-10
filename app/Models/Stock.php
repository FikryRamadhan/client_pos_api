<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        static::creating(function ($stock) {
            if ($stock->type === 'in') {
                $outlet = Outlet::find($stock->outlet_id);
                $currentStock = Stock::where('outlet_id', $stock->outlet_id)
                    ->sum(DB::raw("CASE WHEN type = 'in' THEN quantity WHEN type = 'out' THEN -quantity ELSE 0 END"));

                if ($currentStock + $stock->quantity > $outlet->capacity) {
                    throw new \Exception('Stok melebihi kapasitas outlet.');
                }
            }
        });

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
