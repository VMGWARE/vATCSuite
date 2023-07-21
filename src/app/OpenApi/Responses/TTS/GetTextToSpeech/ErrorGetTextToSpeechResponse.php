<?php

namespace App\OpenApi\Responses\TTS\GetTextToSpeech;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class ErrorGetTextToSpeechResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('error'),
            Schema::string('message')->example('ATIS audio file not found.'),
            Schema::integer('code')->example(404),
            Schema::object('data')->nullable()
        );

        return Response::create('TTSError', 'Error getting TTS file.')
            ->description('Error getting TTS file.')
            ->content(MediaType::json()->schema($response));
    }
}
