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
            Schema::string('message')->example('This ATIS audio file already exists.'),
            Schema::integer('code')->example(409),
            Schema::string('status')->example('error'),
            Schema::object('data')->properties(
                Schema::string('id')->example('5'),
                Schema::string('name')->example('KJAX_ATIS_A_260346Z.mp3'),
                Schema::string('url')->example('/storage/atis/5/KJAX_ATIS_A_260346Z.mp3'),
            )
        );

        return Response::create('ErrorRequestConflictResponse')
            ->description('Request Conflict Errors')
            ->content(MediaType::json()->schema($response));
    }
}
