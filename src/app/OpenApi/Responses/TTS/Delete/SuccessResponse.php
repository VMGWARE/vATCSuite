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
            Schema::string('status')->example('success'),
            Schema::string('message')->example('ATIS audio file deleted successfully.'),
            Schema::integer('code')->example(200),
            Schema::object('data')->nullable()
        );

        return Response::create('DeleteTTSSuccess')
            ->description('Success response')
            ->content(MediaType::json()->schema($response));
    }
}
