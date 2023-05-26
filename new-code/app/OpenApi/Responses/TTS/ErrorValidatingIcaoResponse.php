<?php

namespace App\OpenApi\Responses\TTS;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class ErrorValidatingIcaoResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('error'),
            Schema::string('message')->example('Invalid ICAO code.'),
            Schema::integer('code')->example(400),
            Schema::object('data')->nullable()
        );

        return Response::create('ErrorValidatingIcaoResponse')
            ->description('Information provided in the request was invalid.')
            ->content(MediaType::json()->schema($response));

    }
}
