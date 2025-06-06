<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [];

        for ($i = 0; $i < 5; $i++) {
            $customers[] = [
                'name' => "Customer $i",
                'phone_number' => '08762546271',
                'address' => 'Jln. Raya Sudirman No.17'
            ];
        }

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
