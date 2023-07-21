<?php

namespace App\OpenApi\Responses\TTS\GetTextToSpeech;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class SuccessResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('success'),
            Schema::string('message')->example('ATIS audio file found.'),
            Schema::integer('code')->example(200),
            Schema::object('data')->properties(
                Schema::string('id')->example('1'),
                Schema::string('name')->example('KJAX_ATIS_A_261700Z.mp3'),
                Schema::string('url')->example('/storage/atis/1/KJAX_ATIS_A_261700Z.mp3'),
                Schema::string('expires_at')->example('2021-01-01 00:00:00'),
            )
        );

        return Response::create('GetTextToSpeechSuccess')
            ->description('Get TTS file for an airport.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
