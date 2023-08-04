<?php

namespace App\OpenApi\Responses\TTS;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class ErrorRequestConflictResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('message')
                ->example('This ATIS audio file already exists.')
                ->description('A human-readable message providing additional information about the error.'),
            Schema::integer('code')
                ->example(409)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::string('status')
                ->example('error')
                ->description('The status of the response indicating the error condition.'),
            Schema::object('data')->properties(
                Schema::string('id')
                    ->example('5')
                    ->description('The ID of the existing ATIS audio file.'),
                Schema::string('name')
                    ->example('KJAX_ATIS_A_260346Z.mp3')
                    ->description('The filename of the existing ATIS audio file.'),
                Schema::string('url')
                    ->example('/storage/atis/5/KJAX_ATIS_A_260346Z.mp3')
                    ->description('The URL where the existing ATIS audio file can be accessed.'),
            )
        );

        return Response::create('ErrorRequestConflictResponse')
            ->description('Request Conflict Errors')
            ->content(MediaType::json()->schema($response));
    }
}
