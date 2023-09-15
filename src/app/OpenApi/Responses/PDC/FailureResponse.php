<?php

namespace App\OpenApi\Responses\PDC;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class FailureResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')
                ->example('error')
                ->enum('success', 'error')
                ->description('The status of the response indicating the success or failure of the operation.'),
            Schema::string('message')
                ->example('Validation failed')
                ->description('A human-readable message providing additional information about the status.'),
            Schema::integer('code')
                ->example(200)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')->properties(
                Schema::object('error')->properties(
                    Schema::string('callsign')
                        ->example('The callsign field is required.')
                        ->description('The error message for the callsign field.'),
                    Schema::string('departure')
                        ->example('The departure field is required.')
                        ->description('The error message for the departure field.'),
                    Schema::string('arrival')
                        ->example('The arrival field is required.')
                        ->description('The error message for the arrival field.'),
                    Schema::string('route')
                        ->example('The route field is required.')
                        ->description('The error message for the route field.'),
                    Schema::string('aircraft')
                        ->example('The aircraft field is required.')
                        ->description('The error message for the aircraft field.'),
                    Schema::string('altitude')
                        ->example('The altitude field is required.')
                        ->description('The error message for the altitude field.'),
                    Schema::string('squawk')
                        ->example('The squawk field is required.')
                        ->description('The error message for the squawk field.'),
                    Schema::string('departure_time')
                        ->example('The departure time field is required.')
                        ->description('The error message for the departure time field.'),
                )
            )
        );

        return Response::create('FailedGeneratePDC')
            ->description('Failed to generate a Pre-Departure Clearance (PDC) for a flight.')
            ->content(
                MediaType::json()->schema($response)
            );
    }
}
