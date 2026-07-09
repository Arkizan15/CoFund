<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create 1 Admin
        User::create([
            'name' => 'Admin CoFund',
            'email' => 'admin@cofund.com',
            'password' => Hash::make('password'),
            'role' => RoleEnum::ADMIN->value,
            'email_verified_at' => now(),
            'balance' => 0,
        ]);

        // Create 3 Creator accounts
        $creators = [
            ['name' => 'Rina Wijaya', 'email' => 'rina@cofund.com', 'balance' => 500000],
            ['name' => 'Dimas Pratama', 'email' => 'dimas@cofund.com', 'balance' => 250000],
            ['name' => 'Sari Indah', 'email' => 'sari@cofund.com', 'balance' => 750000],
        ];

        foreach ($creators as $creator) {
            User::create([
                'name' => $creator['name'],
                'email' => $creator['email'],
                'password' => Hash::make('password'),
                'role' => RoleEnum::CREATOR->value,
                'email_verified_at' => now(),
                'balance' => $creator['balance'],
            ]);
        }

        // Create 5 Backer accounts
        $backers = [
            ['name' => 'Budi Santoso', 'email' => 'budi@cofund.com', 'balance' => 1000000],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@cofund.com', 'balance' => 2500000],
            ['name' => 'Adi Nugroho', 'email' => 'adi@cofund.com', 'balance' => 500000],
            ['name' => 'Putri Ayu', 'email' => 'putri@cofund.com', 'balance' => 1500000],
            ['name' => 'Hendra Gunawan', 'email' => 'hendra@cofund.com', 'balance' => 3000000],
        ];

        foreach ($backers as $backer) {
            User::create([
                'name' => $backer['name'],
                'email' => $backer['email'],
                'password' => Hash::make('password'),
                'role' => RoleEnum::BACKER->value,
                'email_verified_at' => now(),
                'balance' => $backer['balance'],
            ]);
        }
    }
}
