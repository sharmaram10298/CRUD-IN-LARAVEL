<?php

use App\Http\Controllers\AdmissionFormController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('index', [AdmissionFormController::class, 'index'])->name('student.index');
Route::get('/', [AdmissionFormController::class, 'create'])->name('student.create'); // Fixed typo in route name
Route::post('store', [AdmissionFormController::class, 'store'])->name('student.store');
Route::get('{id}/edit', [AdmissionFormController::class, 'edit'])->name('student.edit');
Route::put('{id}/update', [AdmissionFormController::class, 'update'])->name('student.update');
// Route::get('{id}/delete', [AdmissionFormController::class, 'delete'])->name('student.delete');
Route::delete('{id}/delete', [AdmissionFormController::class, 'delete'])->name('student.delete');

// Add more routes as needed
