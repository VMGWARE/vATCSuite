<?php

namespace App\OpenApi\RequestBodies\Airport;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class GenerateAtisRequestBody extends RequestBodyFactory
{
    public function build(): RequestBody
    {
        $response = Schema::object()->properties(
            Schema::string('ident')
                ->example('A')
                ->description('The ATIS ident letter.')
                ->required(),
            Schema::string('icao')
                ->example('KJAX')
                ->description('The ICAO airport code.')
                ->required(),
            Schema::string('remarks1')
                ->description('Custom remarks.'),
            Schema::object('remarks2')
                ->description('Array of preset remarks.'),
            Schema::object('landing_runways')
                ->description('Array of landing runways.')
                ->required(),
            Schema::object('departing_runways')
                ->description('Array of departing runways.')
                ->required(),
            Schema::string('output-type')
                ->description('The output type.')
                ->example('atis')
                ->enum('atis', 'awos')
                ->required(),
            Schema::boolean('override_runways')
                ->description('Disable the requirement for a runway to be selected.')
                ->example(false),
        );

        return RequestBody::create('GenerateAtis')
            ->description('Generate ATIS text for an airport.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
