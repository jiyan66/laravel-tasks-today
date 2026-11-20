<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Either all demo data is seeded, or the transaction rolls back.
        DB::transaction(function (): void {
            $this->call([UserSeeder::class, TaskSeeder::class]);
        });
    }
}
