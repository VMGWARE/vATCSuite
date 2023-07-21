<?php

namespace App\OpenApi\Responses\TTS\Delete;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class ErrorMissingIdResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('error'),
            Schema::string('message')->example('You must provide an ATIS ID.'),
            Schema::integer('code')->example(400),
            Schema::object('data')->nullable()
        );

        return Response::create('TTSErrorMissingId')
            ->description('Missing ATIS ID')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
