<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-20 10:00:00', 'Asia/Manila'));
    }

    public function test_seed_data_meets_the_required_counts_and_dates(): void
    {
        $this->seed();

        $this->assertDatabaseCount('tasks', 9);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame(3, Task::query()->distinct()->count('task_date'));
        $this->assertSame(4, Task::query()->dueOn('2026-11-20')->count());
    }

    public function test_welcome_filters_out_both_past_and_future_tasks(): void
    {
        $this->seed();

        $response = $this->get('/')->assertOk();
        $response->assertSee('Build the Welcome page');
        $response->assertSee('Verify the task date filter');
        $response->assertDontSee('Review Laravel MVC notes');
        $response->assertDontSee('Check the hosted application');
        $response->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 4);
    }

    public function test_full_list_displays_every_task_in_date_order(): void
    {
        // Insert out of chronological order to expose missing orderBy clauses.
        Task::query()->create(['title' => 'Future item', 'task_date' => '2026-11-21']);
        Task::query()->create(['title' => 'Past item', 'task_date' => '2026-11-19']);
        Task::query()->create(['title' => 'Current item', 'task_date' => '2026-11-20']);

        $this->get('/tasks')->assertOk()
            ->assertSeeInOrder(['Past item', 'Current item', 'Future item'])
            ->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 3);
    }

    public function test_profile_displays_the_single_seeded_user(): void
    {
        $this->seed();

        $this->get('/profile')->assertOk()
            ->assertSee('Joseph Gian Carlo Mistica')
            ->assertSee('gian.mistica')
            ->assertSee('gian.mistica@example.com');
    }

    public function test_about_identifies_the_developer(): void
    {
        $this->get('/about')->assertOk()
            ->assertSee('Joseph Gian Carlo Mistica')
            ->assertSee('Technical Summative Assessment 4');
    }

    public function test_reseeding_updates_relative_dates_without_duplicate_records(): void
    {
        $this->seed();
        $this->travelTo(CarbonImmutable::parse('2026-11-25 10:00:00', 'Asia/Manila'));
        $this->seed();

        $this->assertDatabaseCount('tasks', 9);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame(4, Task::query()->dueOn('2026-11-25')->count());
        $this->assertSame(0, Task::query()->dueOn('2026-11-20')->count());
    }

    public function test_no_tasks_today_has_an_empty_state_without_hiding_the_full_list(): void
    {
        $this->seed();
        $this->travelTo(CarbonImmutable::parse('2026-12-10 10:00:00', 'Asia/Manila'));

        $this->get('/')->assertOk()->assertSee('No tasks are scheduled for today.');
        $this->get('/tasks')->assertOk()
            ->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 9);
    }

    public function test_database_defaults_status_to_pending(): void
    {
        $task = Task::query()->create(['title' => 'Default status check', 'task_date' => '2026-11-20']);

        $this->assertSame('pending', $task->fresh()->status);
    }

    public function test_username_has_a_database_unique_constraint(): void
    {
        User::query()->create(['username' => 'same', 'full_name' => 'First User', 'email' => 'first@example.com']);

        $this->expectException(QueryException::class);
        User::query()->create(['username' => 'same', 'full_name' => 'Second User', 'email' => 'second@example.com']);
    }

    public function test_task_titles_are_escaped_in_both_listings(): void
    {
        $payload = '<script>alert("test")</script>';
        Task::query()->create(['title' => $payload, 'task_date' => '2026-11-20']);

        foreach (['/', '/tasks'] as $url) {
            $this->get($url)->assertOk()->assertSee($payload)->assertDontSee($payload, false);
        }
    }

    public function test_seeder_preserves_existing_login_accounts_and_passwords(): void
    {
        $this->seed();
        $demo = User::query()->where('username', 'gian.mistica')->firstOrFail();
        $demo->password = 'Testing123!';
        $demo->save();
        $hash = $demo->password;
        $this->signIn();
        $this->seed();
        $this->assertDatabaseCount('users', 2);
        $this->assertSame($hash, $demo->fresh()->password);
        $this->assertDatabaseHas('users', ['username' => 'operator']);
        $this->assertDatabaseCount('tasks', 9);
    }
}
