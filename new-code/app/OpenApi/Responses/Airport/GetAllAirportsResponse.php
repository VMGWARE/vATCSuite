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
            Schema::string('status')->example('success'),
            Schema::string('message')->example('Airports retrieved successfully.'),
            Schema::integer('code')->example(200),
            Schema::array('data')->items(
                Schema::object()->properties(
                    Schema::integer('id')->example(1),
                    Schema::string('icao')->example('AGEV'),
                    Schema::string('name')->example('Geva Airport'),
                    Schema::string('runways')->example('33,15')
                )
            ),
        );

        return Response::create('')
            ->description('')
            ->content(MediaType::json()->schema($response));
    }
}
