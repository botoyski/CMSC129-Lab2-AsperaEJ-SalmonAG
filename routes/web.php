<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::view('profile', 'profile')->name('profile');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{taskId}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
    Route::delete('tasks/{taskId}/force', [TaskController::class, 'forceDestroy'])->name('tasks.force-destroy');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
