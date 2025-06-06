<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Bubur Bayi Wortel & Labu',
                'price' => 15000,
                'stock' => 0, // stok awal sebelum stok masuk
                'description' => 'Bubur bayi dengan campuran wortel dan labu, kaya vitamin A dan serat.'
            ],
            [
                'name' => 'Bubur Bayi Pisang & Apel',
                'price' => 16000,
                'stock' => 0,
                'description' => 'Bubur bayi rasa pisang dan apel yang manis alami dan mudah dicerna.'
            ],
            [
                'name' => 'Bubur Bayi Nasi Merah',
                'price' => 18000,
                'stock' => 0,
                'description' => 'Bubur bayi berbahan dasar nasi merah, kaya serat dan antioksidan.'
            ],
            [
                'name' => 'Bubur Bayi Ubi Ungu',
                'price' => 17000,
                'stock' => 0,
                'description' => 'Bubur bayi dari ubi ungu yang mengandung antioksidan tinggi.'
            ],
            [
                'name' => 'Bubur Bayi Kacang Hijau',
                'price' => 15000,
                'stock' => 0,
                'description' => 'Bubur bayi kacang hijau, kaya protein nabati dan mudah dicerna.'
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
