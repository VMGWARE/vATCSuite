<?php

namespace App\OpenApi\RequestBodies\TTS;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class GetTextToSpeechRequestBody extends RequestBodyFactory
{
    public function build(): RequestBody
    {
        $response = Schema::object()->properties(
            Schema::string('id')
                ->example('1')
                ->description('The ID of the ATIS audio file.')
                ->required(),
            Schema::string('icao')
                ->example('KJAX')
                ->description('The ICAO code of the airport.')
                ->required(),
        );

        return RequestBody::create('GetTextToSpeech')
            ->description('Get TTS file for an airport.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
