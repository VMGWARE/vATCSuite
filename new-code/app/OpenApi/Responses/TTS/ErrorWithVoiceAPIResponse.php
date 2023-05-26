<?php

namespace App\OpenApi\Responses\TTS;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class ErrorWithVoiceAPIResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('error'),
            Schema::string('message')->example('Could not generate ATIS using the VoiceRSS API.'),
            Schema::integer('code')->example(500),
            Schema::object('data')->nullable()
        );

        return Response::create('ErrorWithVoiceAPI')
            ->description('Error with VoiceRSS API.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
