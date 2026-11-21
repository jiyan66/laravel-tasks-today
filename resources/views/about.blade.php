@extends('layouts.app')

@section('title', 'About')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">ABOUT THIS PROJECT</p>
            <h1>A clearer view of your day.</h1>
            <p class="subtitle">A simple place to see today's priorities and the work ahead.</p>
        </div>
    </div>

    <section class="panel about-panel" aria-labelledby="about-heading">
        <h2 id="about-heading">Tasks for Today Management System</h2>
        <p>This application brings today's tasks, the complete task schedule, and a user profile into four public pages, with authenticated task management and completion notifications.</p>
        <p>It was developed for IT0049 Web System Technologies, Technical Summative Assessment 4.</p>

        <div class="about-divider"></div>
        <p class="eyebrow">DEVELOPED BY</p>
        <h2>Joseph Gian Carlo Mistica</h2>
        <p>BS Information Technology &middot; Cybersecurity</p>
        <p>FEU Institute of Technology</p>
    </section>
@endsection
