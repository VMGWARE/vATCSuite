<?php

namespace App\OpenApi\Responses\Airport;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class AtisResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')
                ->example('success')
                ->enum('success', 'error')
                ->description('Status of the response. Can be either "success" or "error".'),
            Schema::string('message')
                ->example('ATIS generated successfully.')
                ->description('A human-readable message providing additional information about the response.'),
            Schema::integer('code')
                ->example(200)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')->properties(
                Schema::string('spoken')
                    ->example('JACKSONVILLE INTERNATIONAL AIRPORT INFORMATION ALPHA...')
                    ->description('Spoken ATIS text.'),
                Schema::string('text')
                    ->example('JACKSONVILLE INTERNATIONAL AIRPORT INFORMATION A...')
                    ->description('Raw ATIS text, without any formatting or converting of letters to words.'),
            )
        );

        return Response::create('AtisSuccess')
            ->description('Response for successful ATIS text generation.')
            ->content(MediaType::json()->schema($response));
    }
}
