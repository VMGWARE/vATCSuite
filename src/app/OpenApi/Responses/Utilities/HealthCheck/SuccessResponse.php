<?php

namespace App\OpenApi\Responses\Utilities\HealthCheck;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class SuccessResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('ok'),
            Schema::string('message')->example('API v1 is up and running!'),
            Schema::integer('code')->example(200),
            Schema::object('data')->properties(
                Schema::string('uptime')->example('3 days 2 hours'),
                Schema::string('timestamp')->example('2021-01-01 00:00:00'),
                Schema::string('app_version')->example('1.0.0'),
                Schema::string('api_version')->example('v1'),
                Schema::number('diskspace')->example(50.0),
                Schema::number('latency')->example(0.123)
            ),
            Schema::object('dependencies')->properties(
                Schema::string('database')->example('OK'),
                Schema::string('storage')->example('OK'),
            )
        );

        return Response::create('GetHealthCheckSuccess')
            ->description('Get API Health Check status.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
