<?php

namespace App\OpenApi\Responses\Airport;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class MetarResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')
                ->example('success')
                ->enum('success', 'error')
                ->description('Status of the response. Can be either "success" or "error".'),
            Schema::string('message')
                ->example('METAR data retrieved successfully.')
                ->description('A human-readable message providing additional information about the response.'),
            Schema::integer('code')
                ->example(200)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')->properties(
                Schema::string('metar')
                    ->example('KJAX 031556Z 05011KT 10SM SCT034 SCT050 29\/21 A3010 RMK AO2 SLP192 T02890206')
                    ->description('The METAR data for the requested airport.')
            )
        );

        return Response::create('MetarResponse')
            ->description('The METAR data for the requested airport.')
            ->content(MediaType::json()->schema($response));
    }
}
