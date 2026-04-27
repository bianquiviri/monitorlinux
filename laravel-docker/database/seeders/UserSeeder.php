<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'bianquiviri@gmail.com'],
            [
                'name' => 'Bianquiviri',
                'password' => Hash::make('!N1k00905'),
            ]
        );
    }
}
