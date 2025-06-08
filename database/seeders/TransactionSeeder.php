<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Transaction::create([
            'cashier_id' => 1,
            'customer_id' => 1,
            'total_price' => 50000,
            'payment_method' => 'cash',
            'created_at' => '2025-04-01'
        ])->transactionDetails()->createMany([
            [
                'product_id' => 1,
                'quantity' => 2,
                'subtotal' => 30000
            ],
            [
                'product_id' => 2,
                'quantity' => 1,
                'subtotal' => 20000
            ]
        ]);

        \App\Models\Transaction::create([
            'cashier_id' => 2,
            'customer_id' => 2,
            'total_price' => 75000,
            'payment_method' => 'cash',
            'created_at' => '2025-04-02'
        ])->transactionDetails()->createMany([
            [
                'product_id' => 3,
                'quantity' => 3,
                'subtotal' => 45000
            ],
            [
                'product_id' => 4,
                'quantity' => 2,
                'subtotal' => 30000
            ]
        ]);
    }
}
