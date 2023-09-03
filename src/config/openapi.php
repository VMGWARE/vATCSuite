<?php

return [

    'collections' => [

        'default' => [

            'info' => [
                'title' => "vATC Suite - Essential ATC Tools for Virtual Skies",
                'description' => 'vATC Suite equips virtual air traffic controllers with tools essential for generating and delivering crucial airport information, including ATIS broadcasts, AWOS weather reports, and pre-departure clearances, all aimed at augmenting realism in online flying networks.<hr>',
                'version' => config('app.version'),
                'contact' => [
                    'name' => 'The vATC Suite Team',
                    'email' => 'hello@atisgenerator.com',
                    'url' => 'https://atisgenerator.com/',
                ],
                'license' => [
                    'name' => 'CC BY-NC-SA 4.0',
                    'url' => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
                    'description' => 'Distributed under the CC BY-NC-SA 4.0 License. Please note there are additional licensing conditions available in the included LICENSE file.',
                ],
            ],

            "servers" => [
                [
                    "url" => "https://atisgenerator.com",
                    "description" => "Production server"
                ],
                [
                    "url" => "https://dev.atisgenerator.com",
                    "description" => "Development server"
                ],
                [
                    "url" => "http://127.0.0.1:8000",
                    "description" => "Local server"
                ]
            ],

            'host' => 'atisgenerator.com',
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
                    'description' => 'Endpoints related to airport functionalities. This includes retrieving METAR data, ATIS information, runway details, and more.',
                ],
                [
                    'name' => 'Text to Speech',
                    'description' => 'Endpoints dedicated to converting text inputs into spoken word audio files, facilitating audible content for users.',
                ],
                [
                    'name' => 'Utilities',
                    'description' => 'Endpoints providing insights and diagnostic details about the API, its health, version, and other utility functions.',
                ],
                [
                    'name' => 'Miscellaneous',
                    'description' => 'A collection of endpoints serving varied purposes, which don\'t particularly belong to the predefined categories.',
                ]
            ],

            'security' => [
                // GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement::create()->securityScheme('JWT'),
            ],

            // Non standard attributes used by code/doc generation tools can be added here
            'extensions' => [
                // 'x-tagGroups' => [
                //     [
                //         'name' => 'General',
                //         'tags' => [
                //             'user',
                //         ],
                //     ],
                // ],
            ],

            // Route for exposing specification.
            // Leave uri null to disable.
            'route' => [
                'uri' => '/openapi',
                'middleware' => [],
            ],

            // Register custom middlewares for different objects.
            'middlewares' => [
                'paths' => [
                    //
                ],
                'components' => [
                    //
                ],
            ],

        ],

    ],

    // Directories to use for locating OpenAPI object definitions.
    'locations' => [
        'callbacks' => [
            app_path('OpenApi/Callbacks'),
        ],

        'request_bodies' => [
            app_path('OpenApi/RequestBodies'),
        ],

        'responses' => [
            app_path('OpenApi/Responses'),
        ],

        'schemas' => [
            app_path('OpenApi/Schemas'),
        ],

        'security_schemes' => [
            app_path('OpenApi/SecuritySchemes'),
        ],
    ],

];
