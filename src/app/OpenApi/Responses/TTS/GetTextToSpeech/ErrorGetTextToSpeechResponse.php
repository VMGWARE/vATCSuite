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
            Schema::string('status')
                ->example('error')
                ->enum('success', 'error')
                ->description('The status of the response indicating the success or failure of the operation.'),
            Schema::string('message')
                ->example('ATIS audio file not found.')
                ->description('A human-readable message providing additional information about the status.'),
            Schema::integer('code')
                ->example(404)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')
                ->nullable()
                ->description('The data returned by the request.')
        );

        return Response::create('TTSError', 'Error getting TTS file.')
            ->description('Error getting TTS file.')
            ->content(MediaType::json()->schema($response));
    }
}
