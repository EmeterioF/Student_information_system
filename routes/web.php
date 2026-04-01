<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/', fn() => view('welcome'));

Route::prefix('students')->name('students.')->group(function () {
    Route::get('/',                [StudentController::class, 'index'])  ->name('index');
    Route::get('/data',            [StudentController::class, 'data'])   ->name('data');
    Route::post('/store',          [StudentController::class, 'store'])  ->name('store');
    Route::get('/edit/{id}',       [StudentController::class, 'edit'])   ->name('edit');
    Route::put('/update/{id}',     [StudentController::class, 'update']) ->name('update');
    Route::delete('/destroy/{id}', [StudentController::class, 'destroy'])->name('destroy');
});