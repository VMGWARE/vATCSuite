<?php

namespace App\OpenApi\Responses\Airport;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class MetarNotFoundResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')
                ->example('error')
                ->enum('success', 'error')
                ->description('Status of the response. Can be either "success" or "error".'),
            Schema::string('message')
                ->example('Could not find METAR data for the requested airport.')
                ->description('A human-readable message providing additional information about the response.'),
            Schema::integer('code')
                ->example(404)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')
                ->nullable()
                ->description('An optional field for additional data related to the error. It can be null when no data is present.'),

        );

        return Response::create('MetarNotFoundResponse')
            ->description('Could not find METAR data for the requested airport.')
            ->content(MediaType::json()->schema($response));
    }
}
