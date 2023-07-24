<?php

return [

    'collections' => [

        'default' => [

            'info' => [
                'title' => "vATC Suite API",
                'description' => 'vATC Suite provides virtual air traffic controllers with essential tools like
                ATIS and AWOS generation to enhance realism in online flying networks.',
                'version' => config('app.version'),
                'contact' => [
                    'name' => 'The vATC Suite Team',
                    'email' => 'hello@atisgenerator.com',
                    'url' => 'https://atisgenerator.com/',
                ],
                'license' => [
                    'name' => 'CC BY-NC-SA 4.0',
                    'url' => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
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
                    'description' => 'Endpoints for getting airport information like current ATIS and METAR.',
                ],
                [
                    'name' => 'Text to Speech',
                    'description' => 'Endpoints for converting text to speech audio files.',
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
