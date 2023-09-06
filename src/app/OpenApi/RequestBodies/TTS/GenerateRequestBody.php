<?php

namespace App\OpenApi\RequestBodies\TTS;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class GenerateRequestBody extends RequestBodyFactory
{
    public function build(): RequestBody
    {
        $response = Schema::object()->properties(
            Schema::string('icao')
                ->example('KJAX')
                ->description('The ATIS ident letter.')
                ->required(),
            Schema::string('ident')
                ->example('A')
                ->description('The ATIS ident letter.')
                ->required(),
            Schema::string('atis')
                ->example('Jacksonville International Airport, information Alpha, time 1700 Zulu, wind 180 at 10, visibility 10, sky clear, temperature 27, dew point 23, altimeter 3002, landing and departing runway 8, advise on initial contact you have information Alpha.')
                ->description('The ATIS message.')
                ->required(),
        );

        return RequestBody::create('GenerateTTS')
            ->description('Generate TTS file for an airport.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
