<?php

namespace Database\Seeders;

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
        $outlets = 20;

        $users = [];

        for ($i = 1; $i <= $outlets; $i++) {
            $users[] = [
                'name' => "Kasir $i",
                'email' => "cashier$i@gmail.com",
                'password' => Hash::make('foobarr'),
                'phone_number' => '08762546271',
                'role' => 'cashier',
            ];
        }

        $admins = [];

        for ($i = 1; $i <= 5; $i++) {
            $admins[] = [
                'name' => "Admin $i",
                'email' => "admin$i@gmail.com",
                'password' => Hash::make('foobarr'),
                'phone_number' => '08762546271',
                'role' => 'admin',
            ];
        }

        foreach ($admins as $admin) {
            User::create($admin);
        }

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
