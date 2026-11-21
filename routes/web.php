<?php
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
// TSA4 explicitly keeps these four pages public.
Route::get('/', [TaskController::class, 'today'])->name('home');
Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::get('/profile', [PublicProfileController::class, 'show'])->name('profile');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::middleware('auth')->group(function (): void {
    Route::resource('tasks', TaskController::class)->except(['index', 'show']);
    Route::get('/dashboard', fn () => redirect()->route('tasks.index'))->name('dashboard');
});
// Actual Breeze-generated login, registration, logout and password routes.
require __DIR__.'/auth.php';
