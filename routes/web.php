<?php

use App\Http\Controllers\Admin\MarkController as AdminMarkController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\ProfessorController as AdminProfessorController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Professor\ModuleController as ProfessorModuleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function (): void {
        Route::resource('modules', AdminModuleController::class)->except(['show']);

        Route::get('modules/{module}/report', [AdminMarkController::class, 'downloadReport'])
            ->name('modules.report');
        Route::post('marks/{mark}/recheck', [AdminMarkController::class, 'requestRecheck'])
            ->name('marks.recheck');

        Route::resource('students', AdminStudentController::class)->except(['show']);
        Route::resource('professors', AdminProfessorController::class)->except(['show']);
    });

Route::middleware(['auth', 'role:professor'])
    ->prefix('professor')
    ->as('professor.')
    ->group(function (): void {
        Route::get('modules', [ProfessorModuleController::class, 'index'])->name('modules.index');
        Route::get('modules/{module}', [ProfessorModuleController::class, 'show'])->name('modules.show');
        Route::post('modules/{module}/students/{student}/mark', [ProfessorModuleController::class, 'storeMark'])
            ->name('modules.students.mark');
    });

require __DIR__.'/auth.php';
