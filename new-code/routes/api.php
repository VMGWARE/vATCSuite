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
                    'url' => 'https://github.com/RedbeardTFL/ATIS_GENERATOR'
                ],
                'license' => [
                    'name' => 'CC BY-NC-SA 4.0',
                    'url' => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
                ],
                'x-logo' => [
                    'url' => '/lib/images/atis_generator_logo_small.png',
                    'altText' => 'Redbeard\'s Atis Generator',
                ]
            ],
            "servers" => [
                [
                    "url" => "https://atis.vahngomes.dev/api/v1",
                    "description" => "Production server"
                ],
                [
                    "url" => "http://127.0.0.1:8000/api/v1",
                    "description" => "Local server"
                ]
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
                            '200' => [
                                "description" => "Airport found",
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/definitions/Airport',
                                        ],
                                    ],
                                ]
                            ],
                            '404' => [
                                "description" => "Airport not found",
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/definitions/ApiResponse',
                                        ],
                                    ],
                                ]
                            ]
                        ],
                        "parameters" => [
                            [
                                "name" => "icao",
                                "in" => "path",
                                "description" => "ICAO code of the airport",
                                "required" => true,
                                "type" => "string",
                                "example" => "EGKK"
                            ],
                        ],
                    ]
                ]
            ],
            "definitions" => [
                "ApiResponse" => [
                    "type" => "object",
                    "properties" => [
                        "status" => [
                            "type" => "string",
                            "description" => "Status of the response",
                        ],
                        "message" => [
                            "type" => "string",
                            "description" => "Message of the response",
                        ],
                        "code" => [
                            "type" => "integer",
                            "description" => "HTTP status code",

                        ],
                        "data" => [
                            "type" => "object",
                            "description" => "Data of the response",
                        ],
                    ],
                ],
                "Airport" => [
                    "type" => "object",
                    "properties" => [
                        "airport" => [
                            "type" => "object",
                            "description" => "Airport data",
                            "properties" => [
                                "id" => [
                                    "type" => "integer",
                                    "description" => "ID of the airport",
                                ],
                                "icao" => [
                                    "type" => "string",
                                    "description" => "ICAO code of the airport",
                                ],
                                "name" => [
                                    "type" => "string",
                                    "description" => "Name of the airport",
                                ],
                                "runways" => [
                                    "type" => "object",
                                    "description" => "Array of runways",
                                ]
                            ]
                        ],
                        "metar" => [
                            "type" => "string",
                            "description" => "METAR of the airport",
                        ],
                        "wind" => [
                            "type" => "object",
                            "description" => "Wind data",
                            "properties" => [
                                "dir" => [
                                    "type" => "integer",
                                    "description" => "Wind direction",
                                ],
                                "speed" => [
                                    "type" => "integer",
                                    "description" => "Wind speed",
                                ],
                                "gust_speed" => [
                                    "type" => "integer",
                                    "description" => "Wind gust",
                                ],
                            ]

                        ],
                        "runways" => [
                            "type" => "object",
                            "description" => "Array of runways",
                            "properties" => [
                                "runway" => [
                                    "type" => "object",
                                    "description" => "Runway data",
                                    "properties" => [
                                        "runway_hdg" => [
                                            "type" => "integer",
                                            "description" => "Runway heading",
                                        ],
                                        "wind_dir" => [
                                            "type" => "string",
                                            "description" => "Wind direction",
                                        ],
                                        "wind_diff" => [
                                            "type" => "string",
                                            "description" => "Wind difference",
                                        ],
                                    ]
                                ],

                            ],
                        ],
                    ],
                ],
            ],

        ]);
    });
});
