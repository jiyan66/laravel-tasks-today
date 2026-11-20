<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A simple task planner built with Laravel and Eloquent.">
    <title>@yield('title', 'Tasks for Today') | Mistica</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark" aria-hidden="true">T</span>
                <span>Tasks for Today<small>YOUR DAILY WORKSPACE</small></span>
            </a>
            <nav aria-label="Main navigation">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Today</a>
                <a href="{{ route('tasks.index') }}" @if(request()->routeIs('tasks.*')) aria-current="page" @endif>Task List</a>
                <a href="{{ route('profile') }}" @if(request()->routeIs('profile')) aria-current="page" @endif>Profile</a>
                <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>About</a>
            </nav>
        </div>
    </header>

    <main id="main-content" class="container">
        @yield('content')
    </main>

    <footer class="site-footer container">
        <span>Tasks for Today</span>
        <span>&copy; {{ now()->year }} Joseph Gian Carlo Mistica</span>
    </footer>
</body>
</html>
