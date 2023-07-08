<?php

namespace App\OpenApi\Responses\TTS;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class ErrorGeneratingResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('error'),
            Schema::string('message')->example('Could not generate ATIS audio file.'),
            Schema::integer('code')->example(422),
            Schema::object('data')->nullable()
        );

        return Response::create('ErrorGeneratingResponse')
            ->description('Could not generate ATIS audio file.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
