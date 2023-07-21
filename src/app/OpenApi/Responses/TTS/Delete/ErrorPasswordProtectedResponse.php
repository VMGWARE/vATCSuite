<?php

namespace App\OpenApi\Responses\TTS\Delete;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class ErrorPasswordProtectedResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('error'),
            Schema::string('message')->example('Password is missing or incorrect.'),
            Schema::integer('code')->example(401),
            Schema::object('data')->nullable()
        );

        return Response::create('ErrorPasswordProtectedResponse')
            ->description('Password is missing or incorrect.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
