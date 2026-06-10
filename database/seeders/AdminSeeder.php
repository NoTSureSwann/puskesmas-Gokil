<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@puskesmas.go.id'],
            [
                'name' => 'Super Admin Puskesmas',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '081234567890',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]
        );
    }
}
