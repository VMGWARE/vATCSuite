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
            Schema::string('status')
                ->example('error')
                ->enum('success', 'error')
                ->description('Status of the response. Can be either "success" or "error".'),
            Schema::string('message')
                ->example('Password is missing or incorrect.')
                ->description('A human-readable message providing additional information about the response.'),
            Schema::integer('code')
                ->example(401)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')
                ->nullable()
                ->description('The data returned by the request.')
        );

        return Response::create('ErrorPasswordProtectedResponse')
            ->description('Response for ATIS audio file deletion failure due to password protection.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
