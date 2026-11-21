@extends('layouts.app')
@section('title', $task->exists ? 'Edit Task' : 'Create Task')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">MANAGE TASKS</p><h1>{{ $task->exists ? 'Edit task' : 'Create task' }}</h1><p class="subtitle">All fields are required.</p></div><a class="button button-light" href="{{ route('tasks.index') }}">Back to tasks</a></div>
    <section class="panel form-panel">
        <form method="POST" action="{{ $task->exists ? route('tasks.update', $task) : route('tasks.store') }}">
            @csrf
            @if ($task->exists) @method('PUT') @endif
            <div class="field"><label for="title">Task title</label><input id="title" name="title" maxlength="150" required value="{{ old('title', $task->title) }}" @error('title') aria-invalid="true" @enderror>@error('title')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="status">Status</label><select id="status" name="status" required @error('status') aria-invalid="true" @enderror>
                @foreach (['pending' => 'Pending', 'in_progress' => 'In progress', 'done' => 'Done'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $task->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>@error('status')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div class="field"><label for="task_date">Scheduled date</label><input id="task_date" name="task_date" type="date" required value="{{ old('task_date', $task->task_date?->toDateString()) }}" @error('task_date') aria-invalid="true" @enderror>@error('task_date')<p class="field-error">{{ $message }}</p>@enderror</div>
            <p class="form-note">Changing an existing task to Done generates a completion notification. Saving an already-done task does not resend it.</p>
            <div class="form-actions"><button class="button" type="submit">{{ $task->exists ? 'Save changes' : 'Create task' }}</button><a href="{{ route('tasks.index') }}">Cancel</a></div>
        </form>
    </section>
@endsection
