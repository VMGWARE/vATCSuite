<?php

namespace App\OpenApi\Responses\Airport;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class GetAllAirportsResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')
                ->example('success')
                ->enum('success', 'error')
                ->description('Status of the response'),
            Schema::string('message')->example('Airports retrieved successfully.')->description('Message of the response'),
            Schema::integer('code')->example(200)->description('Code of the response'),
            Schema::array('data')->items(
                Schema::object()->properties(
                    Schema::integer('id')->example(1)->description('Id of the airport'),
                    Schema::string('icao')->example('AGEV')->description('ICAO of the airport'),
                    Schema::string('name')->example('Geva Airport')->description('Name of the airport'),
                    Schema::string('runways')->example('33,15')->description('Runways of the airport'),
                )
            ),
        );

        return Response::create('GetAllAirports')
            ->description('Get all airports')
            ->content(MediaType::json()->schema($response));
    }
}
