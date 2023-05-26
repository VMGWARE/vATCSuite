<?php

use App\Http\Controllers\API\AirportController;
use App\Http\Controllers\API\TextToSpeechController;
use Illuminate\Support\Facades\Route;

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
        Route::get('/', [AirportController::class, 'all'])->name('api.airport.all');
        Route::get('{icao}', [AirportController::class, 'index'])->name('api.airport.index');
        Route::get('{icao}/runways', [AirportController::class, 'runways'])->name('api.airport.runways');
        Route::post('{icao}/atis', [AirportController::class, 'atis'])->name('api.airport.atis');
        Route::get('{icao}/metar', [AirportController::class, 'metar'])->name('api.airport.metar');

        Route::get('{icao}/tts', [TextToSpeechController::class, 'index']);
        Route::post('{icao}/tts', [TextToSpeechController::class, 'generate']);
        Route::delete('{icao}/tts', [TextToSpeechController::class, 'delete']);

    });
});
