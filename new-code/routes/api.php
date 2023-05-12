<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Airport;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {

    Route::prefix('airport')->group(function () {
        Route::get('{icao}', [Airport::class, 'index'])->name('api.airport.index');
        Route::get('{icao}/runways', [Airport::class, 'runways'])->name('api.airport.runways');
        Route::get('{icao}/atis', [Airport::class, 'atis'])->name('api.airport.atis');
        Route::get('{icao}/metar', [Airport::class, 'metar'])->name('api.airport.metar');
    });
});
