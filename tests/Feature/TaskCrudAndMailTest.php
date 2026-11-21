<?php
namespace Tests\Feature;
use App\Mail\TaskCompleted;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
class TaskCrudAndMailTest extends TestCase
{
    use RefreshDatabase;
    private function task(string $status = 'pending'): Task
    {
        return Task::query()->create(['title' => 'A sample task', 'status' => $status, 'task_date' => today()->toDateString()]);
    }
    private function data(array $changes = []): array
    {
        return array_replace(['title' => 'Updated title', 'status' => 'pending', 'task_date' => today()->toDateString()], $changes);
    }
    public function test_all_four_public_pages_remain_accessible_to_guests(): void
    {
        $this->seed();
        foreach (['/', '/tasks', '/profile', '/about'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Confirm archive');
        }
        $this->assertGuest();
    }
    public function test_all_task_management_routes_require_authentication(): void
    {
        foreach (['create', 'store', 'edit', 'update', 'destroy'] as $action) {
            $route = Route::getRoutes()->getByName('tasks.'.$action);
            $this->assertNotNull($route);
            $this->assertContains('auth', $route->gatherMiddleware());
        }
    }
    public function test_guest_get_and_write_attempts_redirect_without_modifying_data_or_sending_mail(): void
    {
        Mail::fake();
        $task = $this->task();
        $this->get('/tasks/create')->assertRedirect(route('login'));
        $this->get('/tasks/'.$task->id.'/edit')->assertRedirect(route('login'));
        $this->post('/tasks', $this->data())->assertRedirect(route('login'));
        $this->put('/tasks/'.$task->id, $this->data(['status' => 'done']))->assertRedirect(route('login'));
        $this->delete('/tasks/'.$task->id)->assertRedirect(route('login'));
        $this->assertDatabaseCount('tasks', 1);
        $this->assertSame('pending', $task->fresh()->status);
        $this->assertFalse($task->fresh()->is_archived);
        Mail::assertNothingSent();
    }
    public function test_authenticated_create_and_prefilled_edit_forms_work(): void
    {
        $this->signIn();
        $task = $this->task();
        $this->get('/tasks/create')->assertOk()->assertSee('name="_token"', false);
        $this->get('/tasks/'.$task->id.'/edit')->assertOk()->assertSee('value="A sample task"', false)->assertSee('name="_method"', false);
    }
    public function test_valid_create_saves_task_without_a_completion_notification(): void
    {
        Mail::fake();
        $this->signIn();
        $this->post('/tasks', $this->data(['status' => 'done']))->assertRedirect(route('tasks.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', ['title' => 'Updated title', 'status' => 'done', 'is_archived' => false]);
        Mail::assertNothingSent();
    }
    public function test_required_fields_bad_status_invalid_date_and_long_title_are_rejected(): void
    {
        Mail::fake();
        $this->signIn();
        $this->post('/tasks', [])->assertSessionHasErrors(['title', 'status', 'task_date']);
        $this->from('/tasks/create')->post('/tasks', $this->data(['status' => 'completed', 'task_date' => '2026-02-30']))
            ->assertRedirect('/tasks/create')->assertSessionHasErrors(['status', 'task_date'])->assertSessionHasInput('title', 'Updated title');
        $this->post('/tasks', $this->data(['title' => str_repeat('x', 151)]))->assertSessionHasErrors('title');
        $this->assertDatabaseCount('tasks', 0);
        Mail::assertNothingSent();
    }
    public function test_archive_flag_cannot_be_set_through_a_create_or_update_form(): void
    {
        $this->signIn();
        $task = $this->task();
        $this->post('/tasks', $this->data(['is_archived' => true]))->assertSessionHasErrors('is_archived');
        $this->put('/tasks/'.$task->id, $this->data(['is_archived' => true]))->assertSessionHasErrors('is_archived');
        $this->assertFalse($task->fresh()->is_archived);
        $this->assertDatabaseCount('tasks', 1);
    }
    public function test_pending_to_done_sends_one_email_to_the_configured_recipient(): void
    {
        Mail::fake();
        config(['tasks.completion_recipient' => 'configured@example.test']);
        $this->signIn();
        $task = $this->task();
        $this->put('/tasks/'.$task->id, $this->data(['status' => 'done']))->assertRedirect(route('tasks.index'))->assertSessionHasNoErrors();
        $this->assertSame('done', $task->fresh()->status);
        Mail::assertSent(TaskCompleted::class, fn ($mail) => $mail->hasTo('configured@example.test') && $mail->taskId === $task->id && $mail->taskTitle === 'Updated title');
        Mail::assertSentCount(1);
    }
    public function test_updating_an_already_done_task_does_not_send_again(): void
    {
        Mail::fake();
        $this->signIn();
        $task = $this->task();
        $this->put('/tasks/'.$task->id, $this->data(['status' => 'done']))->assertSessionHasNoErrors();
        $this->put('/tasks/'.$task->id, $this->data(['status' => 'done', 'title' => 'Edited after completion']))->assertSessionHasNoErrors();
        Mail::assertSentCount(1);
        $this->assertSame('Edited after completion', $task->fresh()->title);
    }
    public function test_non_completion_status_changes_send_no_mail(): void
    {
        Mail::fake();
        $this->signIn();
        $task = $this->task();
        $this->put('/tasks/'.$task->id, $this->data(['status' => 'in_progress']))->assertSessionHasNoErrors();
        $this->put('/tasks/'.$task->id, $this->data())->assertSessionHasNoErrors();
        Mail::assertNothingSent();
    }
    public function test_in_progress_to_done_also_notifies(): void
    {
        Mail::fake();
        $this->signIn();
        $task = $this->task('in_progress');
        $this->put('/tasks/'.$task->id, $this->data(['status' => 'done']))->assertSessionHasNoErrors();
        Mail::assertSentCount(1);
    }
    public function test_reopening_then_completing_again_is_a_new_transition(): void
    {
        Mail::fake();
        $this->signIn();
        $task = $this->task('done');
        $this->put('/tasks/'.$task->id, $this->data())->assertSessionHasNoErrors();
        $this->put('/tasks/'.$task->id, $this->data(['status' => 'done']))->assertSessionHasNoErrors();
        Mail::assertSentCount(1);
    }
    public function test_invalid_update_preserves_data_and_sends_no_mail(): void
    {
        Mail::fake();
        $this->signIn();
        $task = $this->task();
        $this->put('/tasks/'.$task->id, $this->data(['status' => 'done', 'title' => '']))->assertSessionHasErrors('title');
        $this->assertSame('pending', $task->fresh()->status);
        $this->assertSame('A sample task', $task->fresh()->title);
        Mail::assertNothingSent();
    }
    public function test_transport_failure_rolls_back_the_update_and_retains_form_input(): void
    {
        $this->signIn();
        $task = $this->task();
        Mail::shouldReceive('to')->once()->with('completion@example.test')->andThrow(new \RuntimeException('Simulated mail failure'));
        $this->from('/tasks/'.$task->id.'/edit')->put('/tasks/'.$task->id, $this->data(['status' => 'done']))
            ->assertRedirect('/tasks/'.$task->id.'/edit')->assertSessionHasErrors('task')->assertSessionHasInput('title', 'Updated title');
        $this->assertSame('pending', $task->fresh()->status);
        $this->assertSame('A sample task', $task->fresh()->title);
    }
    public function test_invalid_recipient_rolls_back_without_sending(): void
    {
        Mail::fake();
        config(['tasks.completion_recipient' => 'invalid-address']);
        $this->signIn();
        $task = $this->task();
        $this->put('/tasks/'.$task->id, $this->data(['status' => 'done']))->assertSessionHasErrors('task');
        $this->assertSame('pending', $task->fresh()->status);
        Mail::assertNothingSent();
    }
    public function test_archiving_retains_the_row_and_hides_it_from_both_public_lists(): void
    {
        Mail::fake();
        $this->signIn();
        $task = $this->task();
        $this->delete('/tasks/'.$task->id)->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'A sample task', 'is_archived' => true]);
        $this->assertDatabaseCount('tasks', 1);
        $this->post('/logout');
        $this->get('/')->assertOk()->assertDontSee('A sample task');
        $this->get('/tasks')->assertOk()->assertDontSee('A sample task');
        Mail::assertNothingSent();
    }
    public function test_archived_records_cannot_be_edited_updated_or_archived_again(): void
    {
        Mail::fake();
        $this->signIn();
        $task = $this->task();
        $task->is_archived = true;
        $task->save();
        $this->get('/tasks/'.$task->id.'/edit')->assertNotFound();
        $this->put('/tasks/'.$task->id, $this->data(['status' => 'done']))->assertNotFound();
        $this->delete('/tasks/'.$task->id)->assertNotFound();
        $this->assertSame('pending', $task->fresh()->status);
        Mail::assertNothingSent();
    }
    public function test_missing_task_ids_return_404(): void
    {
        $this->signIn();
        $this->get('/tasks/999999/edit')->assertNotFound();
        $this->put('/tasks/999999', $this->data())->assertNotFound();
        $this->delete('/tasks/999999')->assertNotFound();
    }
    public function test_email_view_contains_completion_details_and_escapes_user_text(): void
    {
        $task = $this->task('done');
        $task->title = '<script>alert("x")</script>';
        $mail = new TaskCompleted($task);
        $html = $mail->render();
        $this->assertStringContainsString('A task has been completed', $html);
        $this->assertStringContainsString(e($task->title), $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString($task->task_date->toDateString(), $html);
    }
}
