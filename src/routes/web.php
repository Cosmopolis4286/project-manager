<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('projects')->group(function () {
    Route::get('', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::post('reorder', [ProjectController::class, 'reorder'])->name('projects.reorder');
});

Route::middleware(['auth'])->prefix('projects/{project}')->group(function () {
    Route::get('tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
});

Route::middleware(['auth'])->prefix('tasks')->group(function () {
    Route::get('', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('{task}', [TaskController::class, 'update'])->name('tasks.update');
});

require __DIR__ . '/auth.php';
