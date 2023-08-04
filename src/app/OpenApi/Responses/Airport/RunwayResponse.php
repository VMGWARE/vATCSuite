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
            Schema::string('status')->example('success')->enum('success', 'error')->description('Status of the response'),
            Schema::string('message')->example('Runways retrieved successfully.')->description('Message of the response'),
            Schema::integer('code')->example(200)->description('Code of the response'),
            Schema::array('data')->items(
                Schema::object()->properties(
                    Schema::string('runway')->example('32')->description('Runway number'),
                    Schema::string('runway_hdg')->example('320')->description('Runway heading'),
                    Schema::string('wind_dir')->example('300')->description('Wind direction'),
                    Schema::string('wind_diff')->example('20')->description('Wind difference over runway'),
                ),
            ),

        );

        return Response::create('GetAirportRunways')
            ->description('Get Airport Runways information')
            ->content(MediaType::json()->schema($response));
    }
}
