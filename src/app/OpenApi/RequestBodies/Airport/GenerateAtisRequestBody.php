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
                ->example('Custom remarks.')
                ->description('Custom remarks.'),
            Schema::object('remarks2')
                ->description('Array of preset remarks.'),
            Schema::array('landing_runways')
                ->description('Array of landing runways.')
                ->example(['08', '26'])
                ->required(),
            Schema::array('departing_runways')
                ->description('Array of departing runways.')
                ->example(['08', '26'])
                ->required(),
            Schema::string('output-type')
                ->description('The output type.')
                ->example('atis')
                ->enum('atis', 'awos')
                ->required(),
            Schema::boolean('override_runways')
                ->description('Disable the requirement for a runway to be selected.')
                ->example(false),
            Schema::string('metar')
                ->description('The METAR to use.')
                ->example('KJAX 201853Z 00000KT 10SM FEW250 28/22 A3000 RMK AO2 SLP159 T02780217')
        );

        return RequestBody::create('GenerateAtis')
            ->description('Generate ATIS text for an airport.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
