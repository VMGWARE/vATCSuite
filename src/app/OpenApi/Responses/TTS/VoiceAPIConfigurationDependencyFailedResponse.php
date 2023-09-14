<?php

namespace App\OpenApi\Responses\TTS;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class VoiceAPIConfigurationDependencyFailedResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')
                ->example('error')
                ->description('The status of the response indicating the error condition.'),
            Schema::string('message')
                ->example('Server voice API configuration error.')
                ->description('A human-readable message providing additional information about the error.'),
            Schema::integer('code')
                ->example(424)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')
                ->nullable()
                ->description('An optional field for additional data related to the error. It can be null when no data is present.')
        );

        return Response::create('VoiceAPIConfigurationDependencyFailed')
            ->description('A server voice API configuration error occurred.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
