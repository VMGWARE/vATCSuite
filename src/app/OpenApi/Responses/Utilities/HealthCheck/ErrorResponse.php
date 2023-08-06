<?php

namespace App\OpenApi\Responses\Utilities\HealthCheck;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class ErrorResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('error')->description('Status of the response')->enum('ok', 'error'),
            Schema::string('message')->example('API v1 is having issues.')->description('Message of the response'),
            Schema::integer('code')->example(503)->description('Code of the response'),
            Schema::object('data')->properties(
                Schema::string('uptime')->example('3 days 2 hours')->description('Uptime of the api'),
                Schema::string('timestamp')->example('2021-01-01 00:00:00')->description('Current timestamp'),
                Schema::string('app_version')->example('1.0.0')->description('Version of the app'),
                Schema::string('api_version')->example('v1')->description('Version of the api'),
                Schema::number('diskspace')->example(50.0)->description('How much diskspace is available on the server'),
                Schema::number('latency')->example(0.123)->description('Latency of the api'),
            )->description('Data containing information about the api and server'),
            Schema::object('dependencies')->properties(
                Schema::string('database')->example('Error')->description('Status of the database')->enum('OK', 'Error'),
                Schema::string('storage')->example('OK')->description('Status of the storage')->enum('OK', 'Error'),
            )->description('Status of the dependencies')
        );

        return Response::create('GetHealthCheckError')
            ->description('Get API Health Check status.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
