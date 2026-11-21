<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $username = 'gian.mistica';

        // Keep other login accounts and existing credential hashes intact.
        User::query()->updateOrCreate(
            ['username' => $username],
            [
                'full_name' => 'Joseph Gian Carlo Mistica',
                'email' => 'gian.mistica@example.com',
            ],
        );
    }
}
