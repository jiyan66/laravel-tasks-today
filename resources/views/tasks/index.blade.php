@extends('layouts.app')

@section('title', 'Task List')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">THE BIG PICTURE</p>
            <h1>Every task, in one place.</h1>
            <p class="subtitle">All non-archived work, ordered from the earliest date to the latest.</p>
        </div>
        <a class="button button-light" href="{{ route('home') }}">Back to today</a>
    </div>

    @auth<div class="create-action"><a class="button" href="{{ route('tasks.create') }}">Create task</a></div>@endauth

    <section class="panel" aria-labelledby="all-tasks-heading">
        <div class="panel-heading">
            <div><h2 id="all-tasks-heading">Full Task List</h2><p>Past, present, and upcoming tasks.</p></div>
            <span class="count">{{ $tasks->count() }} total</span>
        </div>
        @include('tasks.partials.table', ['emptyMessage' => 'There are no tasks to display yet.'])
    </section>
@endsection
