<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $outletNames = [
            'Kertosono',
            'Warujayeng',
            'Ketawang',
            'Gading',
            'Ngronggot',
            'Bogo',
            'Perak',
            'Bulakrejo',
            'Prambon',
            'Pucangsimo',
            'Baleturi',
            'Munung',
            'Tinggar',
            'Jenar',
            'Patianrowo',
            'Tanjungkalang',
            'Lengkong',
            'Plosorejo',
            'Sawahan',
            'Ngasem',
        ];

        foreach ($outletNames as $index => $name) {
            $user = User::create([
                'name' => "Kasir " . ($index + 1),
                'email' => "cashier" . ($index + 1) . "@gmail.com",
                'password' => Hash::make('foobarr'),
                'phone_number' => '08762546271',
                'role' => 'cashier',
            ]);

            $user->outlet()->create([
                'name' => $name,
                'address' => "Jln. $name",
                'capacity' => 100,
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Admin $i",
                'email' => "admin$i@gmail.com",
                'password' => Hash::make('foobarr'),
                'phone_number' => '08762546271',
                'role' => 'admin',
            ]);
        }
    }
}
