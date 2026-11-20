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
                                'badge-completed' => $task->status === 'completed',
                                'badge-progress' => $task->status === 'in_progress',
                                'badge-pending' => $task->status === 'pending',
                            ])>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        </td>
                        <td><time datetime="{{ $task->task_date->toDateString() }}">{{ $task->task_date->format('M d, Y') }}</time></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
