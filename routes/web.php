<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FakeController;
use App\Http\Controllers\UserController;
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

Route::get('/', function () {
    return auth()->user() ? view('dashboard') : view('welcome');
})->name('home');

if (app()->environment(['local', 'testing'])) {
    Route::match(['get', 'post'], '/fakelogin/{id?}', [FakeController::class, 'login']);
    Route::get('fakelogout', [FakeController::class, 'logout']);
}

Route::resource('devices', DeviceController::class);
Route::resource('categories', CategoryController::class);

Route::patch('users/{user}/status', [UserController::class, 'status'])->name('users.status');
Route::patch('users/{user}/role', [UserController::class, 'role'])->name('users.role');
Route::patch('users/{user}/categories', [UserController::class, 'categories'])->name('users.categories');
Route::resource('users', UserController::class)->except('edit', 'destroy');

Route::get('login', function () {
    return 'login';
})->name('login')->middleware('guest');
