<?php

use App\Http\Controllers\Admin\MarkController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\ProfessorController as AdminProfessorController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Professor\ModuleController as ProfessorModuleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'role:professor'])->prefix('professor')->name('professor.')->group(function (): void {
    Route::get('/modules', [ProfessorModuleController::class, 'index'])->name('modules.index');
    Route::get('/modules/{module}', [ProfessorModuleController::class, 'show'])->name('modules.show');
    Route::post('/modules/{module}/students/{student}/mark', [ProfessorModuleController::class, 'storeMark'])->name('modules.students.mark');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::resource('professors', AdminProfessorController::class)->except(['show']);
    Route::resource('students', AdminStudentController::class)->except(['show']);
    Route::resource('modules', AdminModuleController::class)->except(['show']);

    Route::get('modules/{module}/report', [MarkController::class, 'downloadReport'])->name('modules.report');
    Route::post('marks/{mark}/recheck', [MarkController::class, 'requestRecheck'])->name('marks.recheck');
});
