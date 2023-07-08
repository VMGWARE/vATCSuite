<?php

return [

    'collections' => [

        'default' => [

            'info' => [
                'title' => "Redbeard's ATIS Generator",
                'description' => 'A simple to use tool for non VATSIM/IVAO/PilotEdge controllers to generate an ATIS in text and spoken formats.',
                'version' => '2.0.0-beta',
                'contact' => [
                    'name' => 'Atis Support',
                    'email' => 'atis@vahngomes.dev',
                    'url' => 'https://atis.vahngomes.dev/',
                ],
                'license' => [
                    'name' => 'CC BY-NC-SA 4.0',
                    'url' => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
                ],
            ],

            "servers" => [
                [
                    "url" => "https://atis.vahngomes.dev",
                    "description" => "Production server"
                ],
                [
                    "url" => "https://dev-atis.vahngomes.dev",
                    "description" => "Development server"
                ],
                [
                    "url" => "http://127.0.0.1:8000",
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
                    'name' => 'Text to Speech',
                    'description' => 'Text to Speech related endpoints',
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
