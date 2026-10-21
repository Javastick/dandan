<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@dandan.test'],
            [
                'name' => 'Administrator DLH Brebes',
                'phone' => '081234567890',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas@dandan.test'],
            [
                'name' => 'Petugas Lapangan Kebersihan',
                'phone' => '089876543210',
                'role' => 'petugas',
                'password' => Hash::make('petugas123'),
            ]
        );
    }
}
