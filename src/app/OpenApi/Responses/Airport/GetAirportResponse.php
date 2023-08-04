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
            Schema::string('status')->example('success')->enum('success', 'error')->description('Status of the response'),
            Schema::string('message')->example('Airport retrieved successfully.')->description('Message of the response'),
            Schema::integer('code')->example(200)->description('Code of the response'),
            Schema::object('data')->properties(
                Schema::object('airport')
                    ->properties(
                        Schema::string('id')->example('3649')->description('Id of the airport'),
                        Schema::string('icao')->example('KJAX')->description('ICAO of the airport'),
                        Schema::string('name')->example('Jacksonville International Airport')->description('Name of the airport'),
                        Schema::string('runways')->example('26,08,32,14')->description('Runways of the airport'),
                        Schema::string('created_at')->example('2023-05-28T20:25:09.000000Z'),
                        Schema::string('updated_at')->example('2023-05-28T20:25:09.000000Z'),
                    ),
                Schema::string('metar')->example('KJAX 291556Z 30008KT 10SM FEW050 BKN250 29/14 A2992 RMK AO2 SLP131 T02890144')->description('METAR of the airport'),
                Schema::object('wind')
                    ->properties(
                        Schema::string('dir')->example('300')->description('Wind direction of the airport'),
                        Schema::string('speed')->example('08')->description('Wind speed of the airport'),
                        Schema::string('gust_speed')->nullable()->example('12')->description('Wind gust speed of the airport'),
                    ),
                Schema::array('runways')->items(
                    Schema::object()->properties(
                        Schema::string('runway')->example('32')->description('Runway number'),
                        Schema::string('runway_hdg')->example('320')->description('Runway heading'),
                        Schema::string('wind_dir')->example('300')->description('Wind direction'),
                        Schema::string('wind_diff')->example('20')->description('Wind difference over runway'),
                    ),
                ),
            ),
        );

        return Response::create('GetAirport')
            ->description('Get Airport information')
            ->content(MediaType::json()->schema($response));
    }
}
