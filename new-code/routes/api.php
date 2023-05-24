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
        Route::get('/', [Airport::class, 'all'])->name('api.airport.all');
        Route::get('{icao}', [Airport::class, 'index'])->name('api.airport.index');
        Route::get('{icao}/runways', [Airport::class, 'runways'])->name('api.airport.runways');
        Route::post('{icao}/atis', [Airport::class, 'atis'])->name('api.airport.atis');
        Route::get('{icao}/metar', [Airport::class, 'metar'])->name('api.airport.metar');
    });

    Route::get('swagger.json', function () {
        return response()->json([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'Redbeard\'s Atis Generator',
                'description' => 'A simple to use tool for non VATSIM/IVAO/PilotEdge controllers to generate an ATIS in text and spoken formats.',
                'version' => '1.0.0',
                'contact' => [
                    'email' => 'atis@vahngomes.dev',
                    'name' => 'Atis Generator Support',
                ],
                'license' => [
                    'name' => 'CC BY-NC-SA 4.0',
                    'url' => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
                ]
            ],
            "servers" => [
                [
                    "url" => "https://atis.vahngomes.dev/api/v1",
                    "description" => "Production server"
                ],
            ],
            'host' => 'atis.vahngomes.dev',
            'basePath' => '/v1',
            "schemes" => ["https", "http"],
            "securityDefinitions" => [
                "api_key" => [
                    "type" => "apiKey",
                    "name" => "api_key",
                    "in" => "header"
                ],
            ],
            "definitions" => [
                "ApiResponse" => [
                    "type" => "object",
                    "properties" => [
                        "status" => [
                            "type" => "string",
                            "description" => "Status of the response",
                            "example" => "success"
                        ],
                        "message" => [
                            "type" => "string",
                            "description" => "Message of the response",
                            "example" => "Airport found"
                        ],
                        "code" => [
                            "type" => "integer",
                            "description" => "HTTP status code",
                            "example" => 200
                        ],
                        "data" => [
                            "type" => "object",
                            "description" => "Data of the response",
                        ],
                    ],
                ],
            ],
            'tags' => [
                [
                    'name' => 'Airport',
                    'description' => 'Airport related endpoints',
                ],
                [
                    'name' => 'ATIS',
                    'description' => 'ATIS related endpoints',
                ],
                [
                    'name' => 'Runway',
                    'description' => 'Runway related endpoints',
                ],
            ],
            'paths' => [
                '/airport/{icao}' => [
                    'get' => [
                        'tags' => ['Airport'],
                        'summary' => 'Get airport information',
                        'operationId' => 'getAirport',
                        "produces" => ["application/json"],
                        "responses" => [
                            "200" => [
                                "description" => "successful operation",

                            ],
                            '404' => [
                                "description" => "Pet not found"
                            ]
                        ],
                    ]
                ]
            ]

        ]);
    });
});
