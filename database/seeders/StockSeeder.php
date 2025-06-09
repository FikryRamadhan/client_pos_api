<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $outlets = Outlet::all();

        foreach ($outlets as $outlet) {
            foreach ($products as $product) {
                Stock::create([
                    'product_id' => $product->id,
                    'outlet_id' => $outlet->id,
                    'type' => 'in',
                    'quantity' => rand(1, 10), // stok awal antara 1 - 10
                    'note' => 'Initial stock for ' . $product->name . ' at ' . $outlet->name,
                ]);
            }
        }
    }
}
