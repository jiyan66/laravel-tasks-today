@extends('layouts.app')

@section('title', 'Today')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">YOUR DAILY FOCUS</p>
            <h1>A little progress, every day.</h1>
            <p class="subtitle">Welcome. Here is your task list for {{ $today->format('l, F j, Y') }}.</p>
        </div>
        <a class="button" href="{{ route('tasks.index') }}">View all tasks <span aria-hidden="true">&rarr;</span></a>
    </div>

    <section class="stats" aria-label="Today's task summary">
        <div class="stat"><span>Today's tasks</span><strong>{{ $tasks->count() }}</strong></div>
        <div class="stat"><span>Completed</span><strong>{{ $tasks->where('status', 'done')->count() }}</strong></div>
        <div class="stat"><span>In progress</span><strong>{{ $tasks->where('status', 'in_progress')->count() }}</strong></div>
        <div class="stat"><span>Pending</span><strong>{{ $tasks->where('status', 'pending')->count() }}</strong></div>
    </section>

    @auth<div class="create-action"><a class="button" href="{{ route('tasks.create') }}">Create task</a></div>@endauth

    <section class="panel" aria-labelledby="today-heading">
        <div class="panel-heading">
            <div><h2 id="today-heading">Today's tasks</h2><p>One task at a time.</p></div>
            <span class="count">{{ $tasks->count() }} total</span>
        </div>
        @include('tasks.partials.table', ['emptyMessage' => 'No tasks are scheduled for today. Check the full Task List for other dates.'])
    </section>
@endsection
