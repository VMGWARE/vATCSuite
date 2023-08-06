<?php

namespace App\OpenApi\Responses\TTS\Delete;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\Contracts\Reusable;

class SuccessResponse extends ResponseFactory implements Reusable
{
    public function build(): Response
    {
        $response = Schema::object()->properties(
            Schema::string('status')
                ->example('success')
                ->enum('success', 'error')
                ->description('Status of the response. Can be either "success" or "error".'),
            Schema::string('message')
                ->example('ATIS audio file deleted successfully.')
                ->description('A human-readable message providing additional information about the response.'),
            Schema::integer('code')
                ->example(200)
                ->description('An optional status code for the response. It typically indicates the HTTP status code.'),
            Schema::object('data')
                ->nullable()
                ->description('The data returned by the request.')
        );

        return Response::create('DeleteTTSSuccess')
            ->description('Response for successful ATIS audio file deletion.')
            ->content(MediaType::json()->schema($response));
    }
}
