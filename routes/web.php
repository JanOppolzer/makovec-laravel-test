<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FakeController;
use App\Http\Controllers\ShibbolethController;
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

if (app()->environment(['local', 'testing'])) {
    Route::match(['get', 'post'], '/fakelogin/{id?}', [FakeController::class, 'login']);
    Route::get('fakelogout', [FakeController::class, 'logout']);
}

Route::get('/', function () {
    return auth()->user() ? view('dashboard') : view('welcome');
})->name('home');

Route::resource('devices', DeviceController::class);
Route::resource('categories', CategoryController::class);

Route::patch('users/{user}/status', [UserController::class, 'status'])->name('users.status');
Route::patch('users/{user}/role', [UserController::class, 'role'])->name('users.role');
Route::patch('users/{user}/categories', [UserController::class, 'categories'])->name('users.categories');
Route::resource('users', UserController::class)->except('edit', 'destroy');

Route::get('login', [ShibbolethController::class, 'create'])->name('login')->middleware('guest');
Route::get('auth', [ShibbolethController::class, 'store'])->middleware('guest');
Route::get('logout', [ShibbolethController::class, 'destroy'])->name('logout')->middleware('auth');
