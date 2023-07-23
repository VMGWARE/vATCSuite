<?php

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

// Atis Generator
Route::get('/', function () {
    return view('home');
})->name('home');

// API Documentation
Route::get('/docs', function () {
    return view('redoc');
})->name('docs');
