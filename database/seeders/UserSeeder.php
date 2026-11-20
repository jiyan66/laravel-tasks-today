<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $username = 'gian.mistica';

        // Do not delete existing people just to meet the one-user requirement.
        if (User::query()->where('username', '!=', $username)->exists()) {
            throw new RuntimeException(
                'Use a separate tasks database. It already contains other users; no records were deleted.'
            );
        }

        User::query()->updateOrCreate(
            ['username' => $username],
            [
                'full_name' => 'Joseph Gian Carlo Mistica',
                'email' => 'gian.mistica@example.com',
            ],
        );
    }
}
