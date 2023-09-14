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
            Schema::string('status')
                ->example('error')
                ->description('The status of the response indicating the error condition.'),
            Schema::string('message')
                ->example('There was an error generating the ATIS audio file.')
                ->description('A human-readable message providing additional information about the error.'),
            Schema::integer('code')
                ->example(500)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')
                ->nullable()
                ->description('An optional field for additional data related to the error. It can be null when no data is present.')
        );

        return Response::create('ErrorWithVoiceAPI')
            ->description('An error associated with the ATIS voice generation API or the ATIS voice generation process.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
