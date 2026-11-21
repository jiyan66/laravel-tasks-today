<?php
namespace App\Http\Controllers;
use App\Http\Requests\TaskRequest;
use App\Mail\TaskCompleted;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
class TaskController extends Controller
{
    public function today(): View
    {
        $today = today();
        $tasks = Task::query()->active()->dueOn($today->toDateString())->orderBy('id')->get();
        return view('tasks.today', compact('tasks', 'today'));
    }
    public function index(): View
    {
        $tasks = Task::query()->active()->orderBy('task_date')->orderBy('id')->get();
        return view('tasks.index', compact('tasks'));
    }
    public function create(): View
    {
        return view('tasks.form', ['task' => new Task(['status' => 'pending', 'task_date' => today()])]);
    }
    public function store(TaskRequest $request): RedirectResponse
    {
        // TSA4 sends on a STATUS CHANGE in update, not when a record is first created.
        Task::query()->create($request->safe()->only(['title', 'status', 'task_date']));
        return redirect()->route('tasks.index')->with('success', 'Task created.');
    }
    public function edit(Task $task): View
    {
        abort_if($task->is_archived, 404);
        return view('tasks.form', compact('task'));
    }
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        abort_if($task->is_archived, 404);
        $data = $request->safe()->only(['title', 'status', 'task_date']);
        $notified = false;
        try {
            // The lock checks the latest stored status, including competing update requests.
            // One transaction attempt avoids automatic retries that could resend external mail.
            DB::transaction(function () use ($task, $data, &$notified): void {
                $record = Task::query()->lockForUpdate()->findOrFail($task->getKey());
                abort_if($record->is_archived, 404);
                $previousStatus = $record->status;
                $record->fill($data);
                $record->save();
                if ($previousStatus !== 'done' && $record->status === 'done') {
                    $recipient = config('tasks.completion_recipient');
                    if (Validator::make(['recipient' => $recipient], ['recipient' => ['required', 'email']])->fails()) {
                        throw new \RuntimeException('Configure a valid TASK_COMPLETION_RECIPIENT.');
                    }
                    // Synchronous log/test-SMTP mail; a transport failure rolls back this update.
                    Mail::to($recipient)->send(new TaskCompleted($record));
                    $notified = true;
                }
            });
        } catch (HttpExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            return back()->withInput($data)->withErrors([
                'task' => 'The task was not updated. Please try again or ask the maintainer to check the private log.',
            ]);
        }
        return redirect()->route('tasks.index')->with('success', $notified
            ? 'Task updated. Completion notification generated.' : 'Task updated.');
    }
    public function destroy(Task $task): RedirectResponse
    {
        DB::transaction(function () use ($task): void {
            $record = Task::query()->lockForUpdate()->findOrFail($task->getKey());
            abort_if($record->is_archived, 404);
            $record->is_archived = true;
            $record->save();
        });
        return redirect()->route('tasks.index')->with('success', 'Task archived. Its database record was preserved.');
    }
}
