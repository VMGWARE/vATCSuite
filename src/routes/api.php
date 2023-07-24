<?php

use App\Http\Controllers\API\HealthCheckController;
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

// API v1 Routes
Route::prefix('v1')->group(function () {
    // Health check
    Route::get('/health', [HealthCheckController::class, 'index'])->name('api.healthcheck');

    // Airports
    Route::prefix('airports')->group(function () {
        // Get all airports
        Route::get('/', [AirportController::class, 'all'])->name('api.airport.all');

        // Get airport by ICAO
        Route::get('{icao}', [AirportController::class, 'index'])->name('api.airport.index');

        // Get airport runways
        Route::get('{icao}/runways', [AirportController::class, 'runways'])->name('api.airport.runways');

        // Generate ATIS
        Route::post('{icao}/atis', [AirportController::class, 'atis'])->name('api.airport.atis');

        // Get ATIS
        Route::get('{icao}/metar', [AirportController::class, 'metar'])->name('api.airport.metar');
    });

    // Text to Speech
    Route::prefix('tts')->group(function () {
        // Get atis audio file
        Route::get('/', [TextToSpeechController::class, 'index']);

        // Generate atis audio file
        Route::post('/', [TextToSpeechController::class, 'generate']);

        // Delete atis audio file
        Route::delete('/', [TextToSpeechController::class, 'delete']);
    });
});
