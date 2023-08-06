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
            Schema::string('status')->example('success')->enum('success', 'error')->description('The status of the response indicating the success or failure of the operation.'),
            Schema::string('message')->example('ATIS audio file found.')
                ->description('A human-readable message providing additional information about the status.'),
            Schema::integer('code')->example(200)->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')->properties(
                Schema::string('id')->example('1')->description('The ID of the generated TTS file.'),
                Schema::string('name')->example('KJAX_ATIS_A_261700Z.mp3')->description('The filename of the generated TTS file.'),
                Schema::string('url')->example('/storage/atis/1/KJAX_ATIS_A_261700Z.mp3')->description('The URL where the generated TTS file can be accessed.'),
                Schema::string('expires_at')->example('2021-01-01 00:00:00')->description('The date and time when the TTS file will expire and be deleted.'),
            )
        );

        return Response::create('GetTextToSpeechSuccess')
            ->description('Successful TTS file retrieval.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
