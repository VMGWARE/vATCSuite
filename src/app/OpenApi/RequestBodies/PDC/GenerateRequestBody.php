<?php

namespace App\OpenApi\RequestBodies\PDC;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\RequestBodyFactory;

class GenerateRequestBody extends RequestBodyFactory
{
    public function build(): RequestBody
    {
        // Define the schema for the request body
        $response = Schema::object()->properties(
            Schema::string('callsign')
                ->example('AAL123')
                ->required()
                ->description('The callsign of the flight.'),
            Schema::string('departure')
                ->example('KJAX')
                ->required()
                ->description('The departure airport ICAO code.'),
            Schema::string('arrival')
                ->example('KDFW')
                ->required()
                ->description('The arrival airport ICAO code.'),
            Schema::string('route')
                ->example('JAX5 J14 MEI J17 MEM J6 PLESS J180 CYN')
                ->required()
                ->description('The route of the flight.'),
            Schema::string('aircraft')
                ->example('B737')
                ->required()
                ->description('The aircraft type.'),
            Schema::string('altitude')
                ->example('FL350')
                ->required()
                ->description('The altitude of the flight.'),
            Schema::string('squawk')
                ->example('1234')
                ->required()
                ->description('The squawk code of the flight.'),
            Schema::string('departure_time')
                ->example('2023-09-15T14:00:00Z')
                ->required()
                ->description('The departure time of the flight in ISO 8601 format.'),
            Schema::string('remarks')
                ->example('VIP onboard, special meal requests')
                ->description('Any remarks for the flight.'),
        );

        return RequestBody::create('GeneratePDC')
            ->description('Generate a Pre-Departure Clearance (PDC) for a flight.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
