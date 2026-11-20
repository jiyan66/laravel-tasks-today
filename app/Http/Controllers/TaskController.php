<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function today(): View
    {
        $today = today();

        $tasks = Task::query()
            ->dueOn($today->toDateString())
            ->orderBy('id')
            ->get();

        return view('tasks.today', compact('tasks', 'today'));
    }

    public function index(): View
    {
        $tasks = Task::query()
            ->orderBy('task_date')
            ->orderBy('id')
            ->get();

        return view('tasks.index', compact('tasks'));
    }
}
