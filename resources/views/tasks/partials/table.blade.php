@if ($tasks->isEmpty())
    <div class="empty-state"><p>{{ $emptyMessage }}</p></div>
@else
    <div class="table-scroll" role="region" aria-label="Task records" tabindex="0">
        <table>
            <caption class="sr-only">Tasks with their ID, title, status, and scheduled date</caption>
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Task</th>
                    <th scope="col">Status</th>
                    <th scope="col">Scheduled date</th>
                    @auth<th scope="col">Manage</th>@endauth
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                    <tr>
                        <td class="muted">{{ $task->id }}</td>
                        <td class="task-title">{{ $task->title }}</td>
                        <td>
                            <span @class([
                                'badge',
                                'badge-completed' => $task->status === 'done',
                                'badge-progress' => $task->status === 'in_progress',
                                'badge-pending' => $task->status === 'pending',
                            ])>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        </td>
                        <td><time datetime="{{ $task->task_date->toDateString() }}">{{ $task->task_date->format('M d, Y') }}</time></td>
                        @auth
                        <td class="task-actions"><a class="text-link" href="{{ route('tasks.edit', $task) }}">Edit</a>
                            <details><summary>Archive</summary><p>Hide this task while retaining its record?</p><form method="POST" action="{{ route('tasks.destroy', $task) }}">@csrf @method('DELETE')<button class="button button-danger" type="submit">Confirm archive</button></form></details>
                        </td>
                        @endauth
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
