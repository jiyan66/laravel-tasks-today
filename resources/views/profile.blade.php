@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">MEET THE PLANNER</p>
            <h1>Your profile.</h1>
            <p class="subtitle">Account information for this workspace.</p>
        </div>
    </div>

    <section class="panel profile-panel" aria-labelledby="profile-heading">
        @if ($user)
            <div class="profile-intro">
                <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->full_name, 0, 1)) }}</span>
                <div><h2 id="profile-heading">{{ $user->full_name }}</h2><p>{{ '@'.$user->username }}</p></div>
            </div>
            <dl class="profile-details">
                <div><dt>User ID</dt><dd>{{ $user->id }}</dd></div>
                <div><dt>Username</dt><dd>{{ $user->username }}</dd></div>
                <div><dt>Full name</dt><dd>{{ $user->full_name }}</dd></div>
                <div><dt>Email address</dt><dd>{{ $user->email }}</dd></div>
            </dl>
        @else
            <div class="empty-state"><h2 id="profile-heading">Profile unavailable</h2><p>No user record is available yet.</p></div>
        @endif
    </section>
@endsection
