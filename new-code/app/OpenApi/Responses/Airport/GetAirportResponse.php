<?php

namespace App\OpenApi\Responses\Airport;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class GetAirportResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')->example('success'),
            Schema::string('message')->example('Airport retrieved successfully.'),
            Schema::integer('code')->example(200),
            Schema::object('data')->properties(
                Schema::object('airport')
                    ->properties(
                        Schema::string('id')->example('3649'),
                        Schema::string('icao')->example('KJAX'),
                        Schema::string('name')->example('Jacksonville International Airport'),
                        Schema::string('runways')->example('26,08,32,14'),
                        Schema::string('created_at')->example('2023-05-28T20:25:09.000000Z'),
                        Schema::string('updated_at')->example('2023-05-28T20:25:09.000000Z'),
                    ),
                Schema::string('metar')->example('KJAX 291556Z 30008KT 10SM FEW050 BKN250 29/14 A2992 RMK AO2 SLP131 T02890144'),
                Schema::object('wind')
                    ->properties(
                        Schema::string('dir')->example('300'),
                        Schema::string('speed')->example('08'),
                        Schema::string('gust_speed')->nullable(),
                    ),
                Schema::array('runways')->items(
                    Schema::object()->properties(
                        Schema::string('runway')->example('32'),
                        Schema::string('runway_hdg')->example('320'),
                        Schema::string('wind_dir')->example('300'),
                        Schema::string('wind_diff')->example('20'),
                    ),
                ),
            ),
        );

        return Response::create('GetAirport')
            ->description('Get Airport information')
            ->content(MediaType::json()->schema($response));
    }
}
