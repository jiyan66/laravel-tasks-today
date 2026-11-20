<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $today = today()->toImmutable();

        $records = [
            ['Review Laravel MVC notes', 'completed', -1],
            ['Prepare the database schema', 'completed', -1],
            ['Run migrations and seeders', 'completed', 0],
            ['Build the Welcome page', 'in_progress', 0],
            ['Verify the task date filter', 'pending', 0],
            ['Update the project README', 'pending', 0],
            ['Check the hosted application', 'pending', 1],
            ['Review the profile details', 'pending', 1],
            ['Submit the repository and website links', 'pending', 1],
        ];

        foreach ($records as [$title, $status, $dayOffset]) {
            // Fixed demo titles make repeat seeding update dates without duplicates.
            Task::query()->updateOrCreate(
                ['title' => $title],
                [
                    'status' => $status,
                    'task_date' => $today->addDays($dayOffset)->toDateString(),
                ],
            );
        }
    }
}
