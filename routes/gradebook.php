<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\Akademik\GradebookController;

Route::get('/akademik/nilai', [GradebookController::class, 'index'])->name('akademik.nilai-render');
Route::get('/akademik/gradebook', [GradebookController::class, 'index'])->name('akademik.gradebook');

Route::post('/akademik/gradebook/save', [GradebookController::class, 'save'])->name('akademik.gradebook.save');
Route::post('/akademik/gradebook/{code}/submit', [GradebookController::class, 'submit'])->name('akademik.gradebook.submit');
Route::post('/akademik/gradebook/{code}/approve', [GradebookController::class, 'approve'])->name('akademik.gradebook.approve');
Route::post('/akademik/gradebook/{code}/publish', [GradebookController::class, 'publish'])->name('akademik.gradebook.publish');
Route::post('/akademik/gradebook/{code}/lock', [GradebookController::class, 'lock'])->name('akademik.gradebook.lock');
Route::post('/akademik/gradebook/{code}/reopen', [GradebookController::class, 'reopen'])->name('akademik.gradebook.reopen');
