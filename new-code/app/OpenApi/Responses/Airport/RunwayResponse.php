<?php

namespace App\OpenApi\Responses\Airport;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class RunwayResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('success'),
            Schema::string('message')->example('Runways retrieved successfully.'),
            Schema::integer('code')->example(200),
            Schema::array('data')->items(
                Schema::object()->properties(
                    Schema::string('runway')->example('32'),
                    Schema::string('runway_hdg')->example('320'),
                    Schema::string('wind_dir')->example('300'),
                    Schema::string('wind_diff')->example('20'),
                ),
            ),

        );

        return Response::create('GetAirportRunways')
            ->description('Get Airport Runways information')
            ->content(MediaType::json()->schema($response));
    }
}
